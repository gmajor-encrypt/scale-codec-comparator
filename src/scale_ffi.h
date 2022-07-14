// skip include guards
#include <stdio.h>

// compact<u32>
char* compact_u32_encode(unsigned int raw);
unsigned int compact_u32_decode(char* raw);

// option<bool>
char* option_bool_encode(char* raw);
char* option_bool_decode(char* raw);