/*
    2021 cScale - A SCALE Library written in C
    Created by Matthew Darnell
*/
#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include <stdbool.h>
#include <assert.h>
#include "../util/hex.h"
#include "../scale.h"

extern void assert_hash_matches_bytes(uint8_t* bytes, size_t byte_len, const char *hex);

static void run_test(uint64_t value, size_t width, uint8_t is_signed, const char *expected_hex_serialized) {

  scale_fixed_int s_e;
  uint8_t serialized[64] = { 0 };
  uint64_t serialized_len = 0;
  
  printf("\t\tEncoding <%llu>: ", (unsigned long long)value);

  switch (width) {
    case 1: {
      if(is_signed) encode_int_to_fixed_int_scale(&s_e, (int8_t)value);
      else encode_int_to_fixed_int_scale(&s_e, (uint8_t)value);
      break;
    }
    case 2: {
      if(is_signed) encode_int_to_fixed_int_scale(&s_e, (int16_t)value);
      else encode_int_to_fixed_int_scale(&s_e, (uint16_t)value);
      break;
    }
    case 4: {
      if(is_signed) encode_int_to_fixed_int_scale(&s_e, (int32_t)value);
      else encode_int_to_fixed_int_scale(&s_e, (uint32_t)value);
      break;
    }
    case 8: {
      if(is_signed) encode_int_to_fixed_int_scale(&s_e, (int64_t)value);
      else encode_int_to_fixed_int_scale(&s_e, (uint64_t)value);
      break;
    }
    default: {
      fprintf(stderr, "Invalid Byte Width for Fixed Int: %zu\n", width);
      assert(1==0);
    }
  }
  
  char *hex = decode_scale_fixed_to_hex(&s_e);
  uint64_t output = 0;

  scale_fixed_int fixed;
  uint64_t len = 0;
  uint8_t serialized_data[128] = { 1 }; //fill with 1s, make sure we can read data slices
  serialize_fixed_int(&serialized_data[2], &len, &s_e);
  size_t bytes_read = read_fixed_int_from_data(&fixed, width, (bool)is_signed, (const uint8_t*)&serialized_data[2]);
  assert(bytes_read == width);
  char *fixed_read_back_from_data = decode_scale_fixed_to_hex(&fixed);

  assert(strcasecmp(hex, fixed_read_back_from_data) == 0);  //Make sure we can read this data back correctly as a slice of a larger array

  free(fixed_read_back_from_data);
  decode_scale_fixed_int((void*)&output, &s_e);

  printf("output: %llu %llu ", (unsigned long long)value, (unsigned long long)output);
  assert(value == output);
  
  free(hex);
  
  memset(serialized, 0, 64 * sizeof(uint8_t));
  serialized_len = 0;
  
  serialize_fixed_int(serialized, &serialized_len, &s_e);
  
  assert_hash_matches_bytes(serialized, serialized_len, expected_hex_serialized);
  
  if(is_signed) printf(" -- Decoded: <%lld>\n", (signed long long)output);
  else printf(" -- Decoded: <%llu>\n", (unsigned long long)output);

}

static void run_test_128(char *value, uint8_t is_signed, const char *expected_hex_serialized) {
  scale_fixed_int s_e;
  uint8_t serialized[64] = { 0 };
  uint64_t serialized_len = 0;
  char *hex = NULL;
  printf("\n\t\tEncoding 128 bit <%s>: ", value);

  char big_endian[64] = { 0 };
  swap_u128_le_to_be(big_endian, value);

  encode_u128_string_to_fixed_int_scale(&s_e, big_endian);

  hex = decode_scale_fixed_to_hex(&s_e);

  free(hex);
  memset(serialized, 0, 64 * sizeof(uint8_t));
  serialized_len = 0;
  serialize_fixed_int(serialized, &serialized_len, &s_e);
  uint64_t one = 0;
  uint64_t two = 0;
  hex = cscale_byte_array_to_hex(serialized, 8);
  one = strtoull(hex, NULL, 16);
  free(hex);
  hex = cscale_byte_array_to_hex(&serialized[7], 8);
  two = strtoull(hex, NULL, 16);
  printf("Got Number Value: <%llu%llu>\t", (unsigned long long)one, (unsigned long long)two);
  free(hex);
  assert_hash_matches_bytes(serialized, 16, expected_hex_serialized);
}

static void run_test_128_no_swap(char *value, uint8_t is_signed, const char *expected_hex_serialized) {
  scale_fixed_int s_e;
  uint8_t serialized[64] = { 0 };
  uint64_t serialized_len = 0;
  char *hex = NULL;
  printf("\n\t\tEncoding 128 bit Big Endian <%s>: ", value);

  encode_u128_string_to_fixed_int_scale(&s_e, value);

  hex = decode_scale_fixed_to_hex(&s_e);

  free(hex);
  memset(serialized, 0, 64 * sizeof(uint8_t));
  serialized_len = 0;
  serialize_fixed_int(serialized, &serialized_len, &s_e);
  uint64_t one = 0;
  uint64_t two = 0;
  hex = cscale_byte_array_to_hex(serialized, 8);
  one = strtoull(hex, NULL, 16);
  free(hex);
  hex = cscale_byte_array_to_hex(&serialized[7], 8);
  two = strtoull(hex, NULL, 16);
  printf("Got Number Value: <%llu%llu>\t", (unsigned long long)one, (unsigned long long)two);
  free(hex);
  assert_hash_matches_bytes(serialized, 16, expected_hex_serialized);
}


static void run_test_fixed_hex(const char *hex, uint8_t is_signed, uint64_t expected) {
  scale_fixed_int s_e;
  printf("\t\tRe-Encoding: <%s> --- ", hex);
  encode_fixed_hex_to_scale(&s_e, is_signed, hex);
  char *hex_out = decode_scale_fixed_to_hex(&s_e);
  printf("Output: <%s> ", hex_out);
  assert(hex_out);
  assert(strcasecmp(hex, hex_out) == 0);
  assert(strlen(hex) == strlen(hex_out));
  free(hex_out);
  if(is_signed) {
    int64_t out = 0;
    decode_scale_fixed_int((void*)&out, &s_e);
    printf("Yields: %lld\n", (signed long long)out);
    assert((int64_t)expected == out);
  } else {
    uint64_t out = 0;
    decode_scale_fixed_int((void*)&out, &s_e);
    printf("Yields: %llu\n", (unsigned long long)out);
    assert(expected == out);
  }
}

int run_fixed_int_test() {


    printf("\tEncoding Ints to Fixed Scale:\n");
    //int 8
    run_test(0, 1, 1, "00");
    run_test(69, 1, 1, "45");

    //uint 8
    run_test(0, 1, 0, "00");
    run_test(1, 1, 0, "01");


    //int 16
    run_test(0, 2, 1, "0000");
    run_test(128, 2, 1, "8000");

    //uint 16
    run_test(0, 2, 0, "0000");
    run_test(42, 2, 0, "2A00");


    //int 32
    run_test(0, 4, 1, "00000000");
    run_test(1030404040, 4, 1, "C8B76A3D");

    //uint 32
    run_test(0, 4, 0, "00000000");
    run_test(4294967294, 4, 0, "FEFFFFFF");
    run_test(16777215, 4, 0, "FFFFFF00");


    //int 64
    run_test(0, 8, 1, "0000000000000000");


    //uint 64
    run_test(0, 8, 0, "0000000000000000");

    run_test(4294967296, 8, 1, "0000000001000000");
    run_test(4294967296, 8, 0, "0000000001000000");

    run_test_128("0xf7d8fca02ff8ab990000000000000000", 0, "99abf82fa0fcd8f70000000000000000");
    run_test_128("cef512a72670a59a0000000000000000", 0, "9aa57026a712f5ce0000000000000000");
    run_test_128_no_swap("9aa57026a712f5ce0000000000000000", 0, "9aa57026a712f5ce0000000000000000");
    run_test_128_no_swap("9aa57026a712f5ce", 0, "9aa57026a712f5ce0000000000000000");

    printf("\n\n\tEncoding Fixed Scale Hex to Fixed Scale:\n");
    run_test_fixed_hex("45", 0, 69);
    run_test_fixed_hex("8000", 0, 128);
    run_test_fixed_hex("2A", 0, 42);
    run_test_fixed_hex("2A00", 0, 42);
    run_test_fixed_hex("C8B76A3D", 1, 1030404040);
    run_test_fixed_hex("FFFFFF00", 0, 16777215);


  return 0;
}
