// skip include guards
#include <stdio.h>
#include <stdint.h>
#include <stdbool.h>

// encode for compact<u32>
char* compact_u32_encode(unsigned int raw);
// decode for compact<u32>
unsigned int compact_u32_decode(char* raw);

// encode for option<bool>
char* option_bool_encode(char* raw);
// decode for option<bool>
char* option_bool_decode(char* raw);

// encode for bool
bool bool_decode(char* raw);
// decode for bool
char* bool_encode(bool raw);

struct ResultsType {
  unsigned int ok;
  char* err;
};



// encode for results<u32,string>
char* results_encode(struct ResultsType* raw);
// decode for results<u32,string>
struct ResultsType* results_decode(char* raw);

// Struct
struct CodecStruct {
  unsigned int data;
  uint8_t other;
};
// encode for CodecStruct
char* data_struct_encode(struct CodecStruct* raw);
// decode for CodecStruct
struct CodecStruct* data_struct_decode(char* raw);

// Enum
struct EnumStruct{
    unsigned int a;
    unsigned int b;
    unsigned int c;
};
// encode for EnumStruct
char* data_enum_encode(struct EnumStruct* raw);
// decode for EnumStruct
struct EnumStruct*  data_enum_decode(char* raw);

// encode for string
char* string_encode(char* raw);
// decode for string
char* string_decode(char* raw);

// encode for string fixed u32 array
char* fixU32_encode(unsigned int* ptr,size_t length);
// decode for string fixed u32 array
unsigned int* fixU32_decode(char* raw);

// encode for string vec u32
char* vec_u32_encode(unsigned int* ptr,unsigned int length);
// decode for string vec u32
unsigned int* vec_u32_decode(char* raw);

// tuple
struct TupleType {
   unsigned int a;
   unsigned int b;
};
// encode for TupleType(u32,u32)
char* tuple_u32u32_encode(struct TupleType* raw);
// decode for TupleType(u32,u32)
struct TupleType*  tuple_u32u32_decode(char* raw);