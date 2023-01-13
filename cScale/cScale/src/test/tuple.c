/*
    2021 cScale - A SCALE Library written in C
    Created by Matthew Darnell
*/

#include <stdio.h>
#include <string.h>
#include <stdlib.h>
#include <assert.h>
#include <locale.h>
#include "../util/utf8.h"
#include "../scale.h"

extern void assert_hash_matches_bytes(uint8_t* bytes, size_t byte_len, const char *hex);



int run_tuple_test() {
  uint8_t bytes[32] = { 0 };
  size_t len = 0;

  scale_compact_int compact = SCALE_COMPACT_INT_INIT;
  encode_compact(&compact, (uint8_t)3);

  scale_boolean boolean = { 0 };
  encode_boolean(&boolean, false);

  serialize_as_tuple(bytes, &len, COMPACT_INT, (void*)&compact, BOOLEAN, (void*)&boolean);
  printf("\n\tTuple<Compact<u8>, bool> = (3, false)\t");
  assert_hash_matches_bytes(bytes, len, "0c00");

  len = 0;
  scale_fixed_int fixed = { 0 };
  encode_int_to_fixed_int_scale(&fixed, (uint32_t)645);

  scale_vector vec = SCALE_VECTOR_INIT;
  create_string(&vec, (unsigned char*)"Hello, World!", strlen("Hello, World!"));

  serialize_as_tuple(bytes, &len, FIXED_INT, (void*)&fixed, STRING, (void*)&vec);
  cleanup_vector(&vec);
  printf("\n\tTuple<u32, String> = (645, \"Hello, World!\")\t");
  assert_hash_matches_bytes(bytes, len, "850200003448656c6c6f2c20576f726c6421");
  printf("\n");

  return 0;
}
