/*
    2021 cScale - A SCALE Library written in C
    Created by Matthew Darnell
*/
#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include "../scale.h"
#include "hex.h"


char* cscale_fixed_byte_array_to_hex(uint8_t* data, int8_t byte_width) {
  char *encoded = calloc((2 * byte_width) + 1, sizeof(char));
  if(!encoded) {
    fprintf(stderr, "Failed to malloc hex string in getencoded_hex_fromscale_fixed_int\n");
    return NULL;
  }
  int i;
  for(i = (FIXED_INT_MAX_BYTES - byte_width); i < FIXED_INT_MAX_BYTES; i++) {
    char temp[4] = { 0 };
    snprintf(temp, 4, "%02X", data[i]);
    strcat(encoded, temp);
  };
  return encoded;
}

char* cscale_byte_array_to_hex(uint8_t* data, size_t len) {
  char *encoded = calloc( (2 * len) + 1, sizeof(char));
  if(!encoded) {
    fprintf(stderr, "Failed to malloc hex string in getencoded_hex_fromscale_fixed_int\n");
    return NULL;
  }
  size_t i;
  char temp[4];
  for(i = 0; i < len; i++) {
    memset(temp, 0, 4 * sizeof(char));
    snprintf(temp, 4, "%02X", data[i]);
    strcat(encoded, temp);
  };
  return encoded;
}

bool cscale_is_valid_hex(const char *hex) {
  size_t len = strlen(hex);
  int i;
  for(i = 0; i < len; i++) {
    if (hex[i] >= '0' && hex[i] <= '9') {
    } else if (hex[i] >= 'A' && hex[i] <= 'F') {
    } else if (hex[i] >= 'a' && hex[i] <= 'f') {
    } else {
      return false;
    }
  }
  return true;
}


bool cscale_hex_digit_to_bin(const char hex, char *out) {
	if (out == NULL)
		return false;

	if (hex >= '0' && hex <= '9') {
		*out = hex - '0';
	} else if (hex >= 'A' && hex <= 'F') {
		*out = hex - 'A' + 10;
	} else if (hex >= 'a' && hex <= 'f') {
		*out = hex - 'a' + 10;
	} else {
		return false;
	}

	return true;
}

void cscale_print_hash(uint8_t *s, size_t len) {
    for(int i = 0; i < len; i++) {
        printf("%02x", s[i]);
    }
}

//bc-bip39/test/test-utils.c
size_t cscale_hex_to_data(const char *hex, uint8_t **out) {
	if (hex == NULL || *hex == '\0') {
        *out = NULL;
		return 0;
    }
    if (out == NULL) {
        return 0;
    }

	size_t len = strlen(hex);
	if (len % 2 != 0) {
    fprintf(stderr, "Malformed Hex String has an odd-numbered length.(%u)\n", (unsigned)len);
    return 0;
  }
	len /= 2;
	*out = malloc(len);
  memset(*out, 0, len*sizeof(uint8_t));

	for (size_t i = 0; i < len; i++) {
  	char b1;
  	char b2;
		if (!cscale_hex_digit_to_bin(hex[i * 2], &b1) || !cscale_hex_digit_to_bin(hex[i * 2 + 1], &b2)) {
			return 0;
		}
		(*out)[i] = (b1 << 4) | b2;
	}

	return len;
}
