from cffi import FFI
from pathlib import Path
import os


class Codec:
    binPath = Path(os.path.dirname(os.path.abspath(__file__))).parent.parent.absolute()
    ffi = FFI()
    ffi.cdef("""
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
char* fixU32_encode(unsigned int* ptr,size_t length);
unsigned int* fixU32_decode(char* raw);

// vendor
char* vec_u32_encode(unsigned int* ptr,unsigned int length);
unsigned int* vec_u32_decode(char* raw);

// tuple
struct TupleType {
   unsigned int a;
   unsigned int b;
};
char* tuple_u32u32_encode(struct TupleType* raw);
struct TupleType*  tuple_u32u32_decode(char* raw);
        """)
    lib = ffi.dlopen(str(binPath) + '/lib/libscale_ffi.dylib')

    def compact_u32_encode(self, uint32):
        return self.to_utf8(self.lib.compact_u32_encode(uint32))

    def compact_u32_decode(self, raw):
        raw = self.str_to_ffi_string(raw)
        return self.lib.compact_u32_decode(raw)

    # boolValue will be None, True, False
    def option_bool_encode(self, boolOption):
        return self.to_utf8(self.lib.option_bool_encode(boolOption))

    def option_bool_decode(self, raw):
        raw = self.str_to_ffi_string(raw)
        return self.to_utf8(self.lib.option_bool_decode(raw))

    def bool_decode(self, raw):
        raw = self.str_to_ffi_string(raw)
        return self.lib.bool_decode(raw)

    def bool_encode(self, boolValue):
        return self.to_utf8(self.lib.bool_encode(boolValue))




    def to_utf8(self, raw):
        return str(self.ffi.string(raw), 'utf-8')

    def str_to_ffi_string(self, raw):
        return self.ffi.new("char []", raw.encode())


if __name__ == '__main__':
    c = Codec()
