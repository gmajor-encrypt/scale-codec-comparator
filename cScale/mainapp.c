#include <stdio.h>
#include "cScale/src/scale.h"
#include "cScale/src/util/utf8.h"
#include "cScale/src/util/hex.h"
#include "../src/scale_ffi.h"
#include <assert.h>
#include <string.h>
#include <stdlib.h>
#include <stdbool.h>
#include <locale.h>


// Test For FFI Call
void test_ffi_call(){

    assert(strcasecmp(compact_u32_encode(1), "04") == 0);
    assert(compact_u32_decode("04")== 1);

    assert(strcasecmp(option_bool_encode("None"), "00") == 0);
    assert(strcasecmp(option_bool_decode("01"), "true") == 0);

    assert(strcasecmp(bool_encode(true), "01") == 0);
    assert(bool_decode("01") == true);

    struct ResultsType resultTest;
    resultTest.ok= 2;
    resultTest.err= "";
    assert(strcasecmp(results_encode(&resultTest), "0002000000") == 0);

    struct ResultsType resultDecodeRaw = *results_decode("0002000000");
    assert(resultDecodeRaw.ok==2);

    assert(strcasecmp(string_encode("Hamlet"), "1848616d6c6574") == 0);

    uint32_t values[6] = { 1,2,3,4,5,6 };
    assert(strcasecmp(fixU32_encode(values,6), "010000000200000003000000040000000500000006000000") == 0);

    assert(strcasecmp(vec_u32_encode(values,6), "18010000000200000003000000040000000500000006000000") == 0);

    unsigned int *fixU32Ptr = fixU32_decode("010000000200000003000000040000000500000006000000");
    for ( int i = 0; i < 6; i++ ) {
        assert(values[i]==*(fixU32Ptr + i));
    }
}

char *add_hex(char* str)
{
    char *ext = "0x";
    char *with_hex;
    asprintf(&with_hex, "%s%s",ext,str);
    return with_hex;
}

char *to_hash(uint8_t *s, size_t len) {
    char *encoded = calloc((2 * len) + 1, sizeof(char));
    for(int i=0; i < len; i++){
        char temp[4] = { 0 };
        snprintf(temp, 4, "%02x", s[i]);
        strcat(encoded, temp);
    };
    return encoded;

}

void test_scale_boolean(){
    printf("\tBoolean to Scale:\n");
    uint8_t out = 0;
    char *hex = NULL;
    scale_boolean boolean, boolean_decoded;
    encode_boolean(&boolean, true);
    serialize_boolean(&out, &boolean);
    hex = decode_boolean_to_hex(&boolean);
    assert(strcasecmp(hex,add_hex(bool_encode(true)) ) == 0);
    assert(encode_boolean_from_hex(&boolean_decoded, hex) == 0);
    assert(decode_boolean(&boolean_decoded) == bool_decode("01"));
    free(hex);


    out = 1;
    encode_boolean(&boolean, false);
    serialize_boolean(&out, &boolean);
    hex = decode_boolean_to_hex(&boolean);
    assert(strcasecmp(hex,add_hex(bool_encode(false)) ) == 0);
    assert(encode_boolean_from_hex(&boolean_decoded, hex) == 0);
    assert(decode_boolean(&boolean_decoded) == bool_decode("00"));
    free(hex);
}

void test_scale_compact_u32(){
    scale_compact_int compact = SCALE_COMPACT_INT_INIT;
    encode_compact(&compact, (uint32_t)69);
    uint8_t serialized[64] = { 0 };
    size_t serialized_len = 0;
    char *output = decode_compact_to_hex(&compact);
    serialize_compact_int(serialized, (uint64_t *) &serialized_len, &compact);
    uint32_t decoded = strtoul(output, NULL, 16);
    char *hex[80];
    for(int i=0; i < serialized_len; i++){
        sprintf(hex,"%s%02X",hex,serialized[i]);
    }
    assert(strcasecmp(compact_u32_encode(69), hex) == 0);
    assert(compact_u32_decode("1501")== decoded);

    free(output);
}

void test_option_boolean(){
    uint8_t bool_option_serialized[64] = { 0 };
    size_t bool_option_serialized_len = 0;

    serialize_scale_option(bool_option_serialized, &bool_option_serialized_len, None, NULL, 1);
    char hex[80];
    for(int i=0; i < bool_option_serialized_len; i++){
        sprintf(hex,"%s%02x",hex,bool_option_serialized[i]);
    }
    assert(strcasecmp(option_bool_encode("None"), hex) == 0);


    enum scale_option option;
    deserialize_scale_option(&option, bool_option_serialized);
    assert(strcasecmp(option_bool_decode("00"), option==0 ? "None": "True") == 0);
}


void test_results_u32_str(){
    #warning "NOT SUPPORT Results type"
}



void test_string(){
    setlocale(LC_ALL, "");

    const char *strings[4] = {
            (const char*)"Hamlet",
            (const char*)"Война и мир",
            (const char*)"三国演义",
            (const char*)"أَلْف لَيْلَة وَلَيْلَة‎"
    };

    int8_t i;
    scale_vector VecOfStrings = SCALE_VECTOR_INIT;

    for(i=0; i < 4; i++) {
        unsigned char serialized[64] = { 0 };
        size_t len = 0;
        void *string = utf8dup((char*)strings[i]);
        size_t utf_len = utf8size_lazy(string);
        scale_vector scale_string = SCALE_VECTOR_INIT, scale_string_deserialized = SCALE_VECTOR_INIT;
        create_string(&scale_string, (unsigned char*)string, utf_len);
        free(string);
        serialize_string(serialized, &len, &scale_string);

        push_vector(&VecOfStrings, serialized, len);
        deserialize_string(&scale_string_deserialized, serialized);
        assert(memcmp(scale_string.data, scale_string_deserialized.data, scale_string.data_len) == 0);
        cleanup_string(&scale_string);
        assert(strcasecmp(string_encode((char*)strings[i]), to_hash(serialized,len)) == 0);
        assert(memcmp(string_decode(to_hash(serialized,len)),(char*)scale_string_deserialized.data,scale_string.data_len)==0);
        cleanup_string(&scale_string_deserialized);
    }
}


int main()
{
    test_ffi_call();
    test_scale_compact_u32();
    test_option_boolean();
    test_scale_boolean();
    test_results_u32_str();
    test_string();
    printf("test success, no errors\n");
}