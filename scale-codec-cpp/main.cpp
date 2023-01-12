#include <iostream>
#include "../src/scale_ffi.h"
#include <cassert>
#include <dlfcn.h>
#include <scale/scale.hpp>
#include <boost/algorithm/hex.hpp>

using namespace std;

using scale::decode;
using scale::encode;

std::string to_hex(const std::vector<uint8_t>& s) {
    std::stringstream stream;
    for (const auto& v : s)
    {
        stream<< std::setfill('0') << std::setw(sizeof(v) * 2)<< std::hex << +v;
    }
    return stream.str();
}


void TestCompactU32(void* handle){
    scale::ScaleEncoderStream s;
    scale::CompactInteger value = 99999;
    s << value;
    char* (*compact_u32_encode) (unsigned int);
    compact_u32_encode = (char* (*)(unsigned int))dlsym(handle, "compact_u32_encode");
    char *encodeValue= compact_u32_encode(99999);
    assert(strcasecmp(encodeValue,const_cast<char*>(to_hex(s.to_vector()).c_str())) == 0);

    unsigned int (*compact_u32_decode) (char*);
    compact_u32_decode = (unsigned int (*)(char*))dlsym(handle, "compact_u32_decode");
    assert(compact_u32_decode(encodeValue)==scale::decode<scale::CompactInteger>(s.to_vector()).value());

    printf("Test CompactU32 success\n");
}


void TestOptionBool(void* handle){
    scale::ScaleEncoderStream s;
    s << std::nullopt;

    char* (*option_bool_encode) (char*);
    option_bool_encode = (char* (*)(char*))dlsym(handle, "option_bool_encode");
    assert(strcasecmp(option_bool_encode((char *)"None"), const_cast<char*>(to_hex(s.to_vector()).c_str())) == 0);

    char* (*option_bool_decode) (char*);
    option_bool_decode = (char* (*)(char*))dlsym(handle, "option_bool_decode");

    using optbool = std::optional<bool>;
    scale::decode<scale::OptionalBool>(scale::ByteArray{1}).value();

    assert(strcasecmp(option_bool_decode((char *)"01"), "true") == 0);

    printf("Test Option<bool> success\n");
}

void TestBool(void* handle){
    scale::ScaleEncoderStream s;
    s << true;

    char* (*bool_encode) (bool);
    bool_encode = (char* (*)(bool))dlsym(handle, "bool_encode");

    assert(strcasecmp(bool_encode(true),const_cast<char*>(to_hex(s.to_vector()).c_str())) == 0);

    bool (*bool_decode) (char*);
    bool_decode = (bool (*)(char*))dlsym(handle, "bool_decode");
    assert(bool_decode("01") == scale::decode<bool>(scale::ByteArray{1}).value());
    printf("Test Bool success\n");
}


struct MyType {
    uint32_t data = 0;
    uint8_t other;
};
scale::ScaleEncoderStream &operator<<(scale::ScaleEncoderStream &s, const MyType &v) {
    return s << v.data << v.other;
}
scale::ScaleDecoderStream &operator>>(scale::ScaleDecoderStream &s, MyType &v) {
    return s >> v.data >> v.other;
}


void TestStruct(void* handle){
    char* (*struct_encode) (CodecStruct*);
    struct_encode = (char* (*)(CodecStruct*))dlsym(handle, "data_struct_encode");

    struct CodecStruct resultTest = {};
    resultTest.data= 10;
    resultTest.other= 1;

    MyType v = {10, 1};
    scale::ScaleEncoderStream s;
    s << v;
    std::vector<uint8_t> encodeData = s.to_vector();
    assert(strcasecmp(struct_encode(&resultTest), const_cast<char*>(to_hex(encodeData).c_str())) == 0);

    MyType v2;
    scale::ScaleDecoderStream s2{encodeData};
    s2 >> v2;

    CodecStruct* (*struct_decode) (char*);
    struct_decode = (CodecStruct* (*)(char*))dlsym(handle, "data_struct_decode");
    struct CodecStruct decodeRaw = *struct_decode("0a00000001");
    assert(decodeRaw.data==v2.data);
    assert(decodeRaw.other==v2.other);

    printf("Test Struct success\n");
}

void TestTuple(void* handle){
    char* (*tuple_encode) (TupleType*);
    tuple_encode = (char* (*)(TupleType*))dlsym(handle, "tuple_u32u32_encode");

    struct TupleType resultTest = {};
    resultTest.a= 10;
    resultTest.b= 1;

    uint32_t a = 10;
    uint32_t b = 1;
    scale::ScaleEncoderStream s;
    s << std::make_tuple(a,b);
    std::vector<uint8_t> encodeData = s.to_vector();
    assert(strcasecmp(tuple_encode(&resultTest), const_cast<char*>(to_hex(encodeData).c_str())) == 0);

    using tuple_type = std::tuple<uint32_t, uint32_t>;
    tuple_type tuple{};
    scale::ScaleDecoderStream s2{encodeData};
    s2 >> tuple;

    TupleType* (*tuple_decode) (char*);
    tuple_decode = (TupleType* (*)(char*))dlsym(handle, "tuple_u32u32_decode");
    struct TupleType decodeRaw = *tuple_decode("0a00000001000000");

    auto &&[a1, b1] = tuple;
    assert(decodeRaw.a==a1);
    assert(decodeRaw.b==b1);

    printf("Test Tuple success\n");
}


void TestEnum(void* handle){
    char* (*enum_encode) (EnumStruct*);
    enum_encode = (char* (*)(EnumStruct*))dlsym(handle, "data_enum_encode");

    struct EnumStruct resultTest = {};
    resultTest.a= 10;
    enum class TEnum : uint32_t { A = 10, B = 0, C = 0 };

    scale::ScaleEncoderStream s;
    s << TEnum::A;
    std::vector<uint8_t> encodeData = s.to_vector();

    assert(strcasecmp(enum_encode(&resultTest), const_cast<char*>(to_hex(encodeData).c_str())) == 0);
    TEnum te{};
    scale::ScaleDecoderStream s2{encodeData};
    s2 >> te;
    EnumStruct* (*enum_decode) (char*);
    enum_decode = (EnumStruct* (*)(char*))dlsym(handle, "data_enum_decode");
    struct EnumStruct decodeRaw = *enum_decode("0001000000");
    assert(decodeRaw.a==10);
    printf("Test Enum success\n");
}



void TestString(void* handle){
    scale::ScaleEncoderStream s;
    s << "Hamlet";
    char* (*string_encode) (char*);
    string_encode = (char* (*)(char*))dlsym(handle, "string_encode");
    assert(strcasecmp(string_encode("Hamlet"), const_cast<char*>(to_hex(s.to_vector()).c_str())) == 0);

    char* (*string_decode) (char*);
    string_decode = (char* (*)(char*))dlsym(handle, "string_decode");

    auto bytes = scale::ByteArray{24, 'h', 'a', 'm', 'l', 'e', 't'};
    scale::ScaleDecoderStream sd( bytes);
    std::string v;
    sd >> v;
    assert(strcasecmp(string_decode("1848616d6c6574"), v.c_str())==0);


    printf("Test String success\n");
}


void TestArray(void* handle){
    char* (*vec_u32_encode) (unsigned int*,unsigned int);
    vec_u32_encode = (char* (*)(unsigned int*,unsigned int))dlsym(handle, "vec_u32_encode");
    uint32_t values[6] = { 1,2,3,4,5,6 };

    std::vector<uint32_t> coll_ui32 = {1,2,3,4,5,6};
    scale::ScaleEncoderStream s;
    s << coll_ui32;
    std::vector<uint8_t> encodeData = s.to_vector();
    assert(strcasecmp(vec_u32_encode(values,6),const_cast<char*>(to_hex(encodeData).c_str())) == 0);

    unsigned int* (*vec_u32_decode) (char*);
    vec_u32_decode = (unsigned int* (*)(char*))dlsym(handle, "vec_u32_decode");
    unsigned int *fixU32Ptr = vec_u32_decode("18010000000200000003000000040000000500000006000000");

    std::vector<uint32_t> coll_ui32_value;
    scale::ScaleDecoderStream s2{encodeData};
    s2>>coll_ui32_value;

    for ( int i = 0; i < 6; i++ ) {
        assert(coll_ui32_value[i]==*(fixU32Ptr + i));
    }


    printf("Test Vec<u32> success\n");
}


void TestScale()
{
    scale::ScaleEncoderStream s;
    uint32_t ui32 = 123u;
    uint8_t ui8 = 234u;
    std::string str = "asdasdasd";
    auto * raw_str = "zxczxczx";
    bool b = true;
    scale::CompactInteger ci = 123456789;
    boost::variant<uint8_t, uint32_t, scale::CompactInteger> vint = scale::CompactInteger(12345);
    std::optional<std::string> opt_str = "asdfghjkl";
    std::optional<bool> opt_bool = false;
    std::pair<uint8_t, uint32_t> pair{1u, 2u};
    std::vector<uint32_t> coll_ui32 = {1u, 2u, 3u, 4u};
    std::vector<std::string> coll_str = {"asd", "fgh", "jkl"};
    std::vector<std::vector<int32_t>> coll_coll_i32 = {{1, 2, 3}, {4, 5, 6, 7}};

    try {
        s << ui32 << ui8 << str << raw_str << b << ci << vint;
        s << opt_str << opt_bool << pair << coll_ui32 << coll_str << coll_coll_i32;
    } catch (std::runtime_error &e) {
    }

    scale::ByteArray data = s.to_vector();

    std::stringstream stream;
    for (const auto num : data) {
        stream << std::hex << std::setw(2) << std::setfill('0') << num;
    }
//    const auto hex_str = hex_representation(data);
    std::cout << stream.str() << '\n';
}



int main() {
    void* handle = dlopen("../lib/libscale_ffi.dylib", RTLD_NOW);
    if (!handle) {
        cerr << "Cannot open library: " << dlerror() << '\n';
        return 1;
    }

    TestCompactU32(handle);
    TestOptionBool(handle);
    TestBool(handle);
    TestStruct(handle);
    TestString(handle);
    TestTuple(handle);
    TestArray(handle);
    TestEnum(handle);

    dlclose(handle);
    std::cout << "Warning: Not support Results type"<< '\n';
    std::cout << "Warning: Not support fixed array type"<< '\n';
    return 0;
}
