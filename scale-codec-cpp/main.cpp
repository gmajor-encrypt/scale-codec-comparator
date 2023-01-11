#include <iostream>
#include "../src/scale_ffi.h"
#include <cassert>
#include <dlfcn.h>
#include <scale/scale.hpp>
#include <boost/algorithm/hex.hpp>
//#include "scale-codec-cpp/include/scale/scale.hpp"
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

    printf("TestCompactU32 success\n");
}



void TestOptionBoolEncode(void* handle){
    char* (*option_bool_encode) (char*);
    option_bool_encode = (char* (*)(char*))dlsym(handle, "option_bool_encode");
    assert(strcasecmp(option_bool_encode((char *)"None"), "00") == 0);
    printf("TestOptionBoolEncode success\n");
}

void TestOptionBoolDecode(void* handle){
    char* (*option_bool_decode) (char*);
    option_bool_decode = (char* (*)(char*))dlsym(handle, "option_bool_decode");
    assert(strcasecmp(option_bool_decode((char *)"01"), "true") == 0);
    printf("TestOptionBoolDecode success\n");
}

void TestBoolEncode(void* handle){
    char* (*bool_encode) (bool);
    bool_encode = (char* (*)(bool))dlsym(handle, "bool_encode");
    assert(strcasecmp(bool_encode(true), "01") == 0);
    printf("TestBoolEncode success\n");
}

void TestBoolDecode(void* handle){
    bool (*bool_decode) (char*);
    bool_decode = (bool (*)(char*))dlsym(handle, "bool_decode");
    assert(bool_decode("01") == true);
    printf("TestBoolEncode success\n");
}

void TestResultEncode(void* handle){
    char* (*results_encode) (ResultsType*);
    results_encode = (char* (*)(ResultsType*))dlsym(handle, "results_encode");
    struct ResultsType resultTest = {};
    resultTest.ok= 2;
    resultTest.err= "";
    assert(strcasecmp(results_encode(&resultTest), "0002000000") == 0);
    printf("TestResultEncode success\n");
}

void TestResultDecode(void* handle){
    ResultsType* (*results_decode) (char*);
    results_decode = (ResultsType* (*)(char*))dlsym(handle, "results_decode");
    struct ResultsType resultDecodeRaw = *results_decode("0002000000");
    assert(resultDecodeRaw.ok==2);
    printf("TestResultDecode success\n");
}

void TestStringEncode(void* handle){
    char* (*string_encode) (char*);
    string_encode = (char* (*)(char*))dlsym(handle, "string_encode");
    assert(strcasecmp(string_encode("Hamlet"), "1848616d6c6574") == 0);
    printf("TestStringEncode success\n");
}

void TestFixU32Encode(void* handle){
    char* (*fixU32_encode) (unsigned int*,unsigned int);
    fixU32_encode = (char* (*)(unsigned int*,unsigned int))dlsym(handle, "fixU32_encode");
    uint32_t values[6] = { 1,2,3,4,5,6 };
    assert(strcasecmp(fixU32_encode(values,6), "010000000200000003000000040000000500000006000000") == 0);
    printf("TestFixU32Encode success\n");
}

void TestVecU32Encode(void* handle){
    char* (*vec_u32_encode) (unsigned int*,unsigned int);
    vec_u32_encode = (char* (*)(unsigned int*,unsigned int))dlsym(handle, "vec_u32_encode");
    uint32_t values[6] = { 1,2,3,4,5,6 };
    assert(strcasecmp(vec_u32_encode(values,6), "18010000000200000003000000040000000500000006000000") == 0);
    printf("TestVecU32Encode success\n");
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
    TestOptionBoolEncode(handle);
    TestOptionBoolDecode(handle);
    TestBoolEncode(handle);
    TestBoolDecode(handle);
    TestResultEncode(handle);
    TestResultDecode(handle);
    TestStringEncode(handle);
    TestFixU32Encode(handle);
    TestVecU32Encode(handle);
    dlclose(handle);
    return 0;
}
