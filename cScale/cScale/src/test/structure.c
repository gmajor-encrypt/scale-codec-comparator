/*
    2021 cScale - A SCALE Library written in C
    Created by Matthew Darnell
*/
#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include <stdbool.h>
#include <assert.h>
#include "../scale.h"

struct BoolTest {
  bool a;
  bool b;
  int8_t c;
  scale_structure scaleencoder;
};

struct MyStruct {
  uint32_t a;
  uint64_t b;
  struct BoolTest c;
  scale_structure scaleencoder;
};


void struct_BoolTest_deserialize(void *structure_out, uint8_t *bytes, size_t len) { //data is valid scale
  struct BoolTest *_struct = (struct BoolTest*)structure_out;
  scale_boolean bool_a;
  scale_boolean bool_b;
  deserialize_boolean(&bool_a, bytes);
  deserialize_boolean(&bool_b, bytes + 1);
  _struct->a = decode_boolean(&bool_a);
  _struct->b =decode_boolean(&bool_b);
  deserialize_fixed_int(&_struct->c, &bytes[2], 1, true);
}

void struct_BoolTest_serialize(uint8_t* serialized, size_t *bytes, void *structure) {
  struct BoolTest *value = (struct BoolTest*)structure;
  scale_boolean bool_a, bool_b;
  scale_fixed_int int8_c;
  encode_boolean(&bool_a, value->a);
  encode_boolean(&bool_b, value->b);
  serialize_boolean(serialized, &bool_a);
  serialize_boolean(&serialized[1], &bool_b);
  *bytes = 2;
  encode_int_to_fixed_int_scale(&int8_c, value->c);
  uint64_t len = 0;
  serialize_fixed_int(&serialized[2], &len, &int8_c);
  *bytes += len;
}

void struct_MyStruct_deserialize(void *structure_out, uint8_t *bytes, size_t len) { //data is valid scale
  struct MyStruct *_struct = (struct MyStruct*)structure_out;
  struct BoolTest *c = (struct BoolTest*)(&_struct->c);
  deserialize_fixed_int(&_struct->a, bytes, 4, false);
  deserialize_fixed_int(&_struct->b, &bytes[4], 8, false);
  c->scaleencoder.deserialize(c, &bytes[12], len-12);
}

void struct_MyStruct_serialize(uint8_t* serialized, size_t *bytes, void *structure) {
  struct MyStruct *value = (struct MyStruct*)structure;
  scale_fixed_int a, b;
  struct BoolTest *c = (struct BoolTest*)&value->c;

  encode_int_to_fixed_int_scale(&a, value->a);
  encode_int_to_fixed_int_scale(&b, value->b);
  uint64_t len = 0;
  serialize_fixed_int(serialized, &len, &a);
  *bytes = len;
  len = 0;
  serialize_fixed_int(&serialized[*bytes], &len, &b);
  *bytes += len;

  size_t booltest_len = 0;
  c->scaleencoder.serialize(&serialized[*bytes], &booltest_len, c);
  *bytes += booltest_len;
}

int run_structure_test() {
  printf("\tEncoding Structures to Fixed Scale:\n");

  //User Defined Struct
  struct BoolTest bool_test;
  bool_test.a = false;
  bool_test.b = true;
  bool_test.c = 42;
  bool_test.scaleencoder.serialize = &struct_BoolTest_serialize;
  bool_test.scaleencoder.deserialize = &struct_BoolTest_deserialize;

  struct MyStruct t;
  t.a = 1;
  t.b = 2;
  t.c = bool_test;
  t.scaleencoder.serialize = &struct_MyStruct_serialize;
  t.scaleencoder.deserialize = &struct_MyStruct_deserialize;

  uint8_t bytes[32] = { 0 };
  size_t len = 0;
  printf("\t\tSerializing Struct {\n\t\t\ta: <u32> = %u\n\t\t\tb: <u64> = %llu\n\t\t\tc: { \n\t\t\t\ta: <bool> = %d\n\t\t\t\tb: <bool> = %d\n\t\t\t\tc: <int8_t> = %d\n\t\t\t} \n    \t\t\t}\n",
  t.a, (unsigned long long)t.b, t.c.a, t.c.b, t.c.c);

  t.scaleencoder.serialize(bytes, &len, &t);
  int i;
  printf("\t\tBytes: [ ");
  for(i = 0; i < len; i++) {
    printf("%02X ", bytes[i]);
  }
  printf("]\n");

  struct MyStruct decoded = { 0 };
  decoded.c.scaleencoder.serialize = &struct_BoolTest_serialize;
  decoded.c.scaleencoder.deserialize = &struct_BoolTest_deserialize;

  t.scaleencoder.deserialize(&decoded, bytes, len);

  printf("\t\tDeserialized Struct {\n\t\t\ta: <u32> = %u\n\t\t\tb: <u64> = %llu\n\t\t\tc: { \n\t\t\t\ta: <bool> = %d\n\t\t\t\tb: <bool> = %d\n\t\t\t\tc: <int8_t> = %d\n\t\t\t} \n    \t\t\t}\n",
  decoded.a, (unsigned long long)decoded.b, decoded.c.a, decoded.c.b, decoded.c.c);

  assert(t.a == decoded.a && t.b == decoded.b && t.c.a == decoded.c.a && t.c.b == decoded.c.b && t.c.c == decoded.c.c);
  return 0;
}
