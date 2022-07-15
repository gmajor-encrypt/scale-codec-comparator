// skip include guards
#include <stdio.h>
#include <stdbool.h>

// compact<u32>
char* compact_u32_encode(unsigned int raw);
unsigned int compact_u32_decode(char* raw);

// option<bool>
char* option_bool_encode(char* raw);
char* option_bool_decode(char* raw);

// bool
bool bool_decode(char* raw);
char* bool_encode(bool raw);

// results<u32,string>
struct ResultsType {
  unsigned int ok;
  char* err;
};
char* results_encode(unsigned int u,char* err,char* result);
struct ResultsType* results_decode(char* raw);

// Struct
struct CodecStruct {
  unsigned int data;
  uint8_t other;
};
char* data_struct_encode(struct CodecStruct* raw);
struct CodecStruct* data_struct_decode(char* raw);

// Enum
struct EnumStruct{
    unsigned int a;
    unsigned int b;
    unsigned int c;
};
char* data_enum_encode(struct EnumStruct* raw);
struct EnumStruct*  data_enum_decode(char* raw);

// string
// option<bool>
char* string_encode(char* raw);
char* string_decode(char* raw);

// fixed
char* fixU16_encode(char* ptr,int length);
char* fixU16_decode(char* raw);

// vendor
char* array_u32_encode(char* ptr,int length);
char* array_u32_decode(char* raw);

// tuple
struct TupleType {
   unsigned int a;
   unsigned int b;
};
char* tuple_u32u32_encode(struct TupleType* raw);
struct TupleType*  tuple_u32u32_decode(char* raw);