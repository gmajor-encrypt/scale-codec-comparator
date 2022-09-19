#include <stdio.h>
#include "cScale/src/scale.h"
#include "../src/scale_ffi.h"

int main()
{
    printf("%s", compact_u32_encode(1));

//    scale_fixed_int fixed = { 0 };
//    encode_int_to_fixed_int_scale(&fixed, (uint16_t)42);
//    uint8_t serialized[64] = { 0 };
//    size_t serialized_len = 0;
//    serialize_fixed_int(serialized, &serialized_len, &fixed);
//
//    uint16_t output = 0;
//    decode_scale_fixed_int((void*)&output, &fixed);
//
//    for(int i=0; i < serialized_len; i++) printf("%02X", serialized[i]);
//    printf(" --- %u\n", output);
//
//
//    scale_compact_int compact = SCALE_COMPACT_INT_INIT;
//    encode_compact(&compact, (uint32_t)69);
//    uint8_t serialized[64] = { 0 };
//    size_t serialized_len = 0;
//    char *output = decode_compact_to_hex(&compact);
//    serialize_compact_int(serialized, &serialized_len, &compact);
//    uint32_t decoded = strtoul(output, NULL, 16);
//    printf("SCALE=<");
//    for(int i=0; i < serialized_len; i++) printf("%02X", serialized[i]);
//    printf("> --- Hex=<%s> --- Decoded=<%u>\n", output, decoded);
//    free(output);
}