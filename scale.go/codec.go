package scale_go

/*
#cgo LDFLAGS: -L../lib -lscale_ffi
#include <stdlib.h>
#include "../src/scale_ffi.h"
*/
import (
	"C"
)

func CompactU32Encode() string {
	o := C.compact_u32_encode(C.uint(2))
	return C.GoString(o)
}

func CompactU32Decode() uint {
	o := C.compact_u32_decode(C.CString("08"))
	output := (C.uint)(o)
	return uint(output)
}

func OptionBoolEncode() string {
	o := C.option_bool_encode(C.CString("None"))
	return C.GoString(o)
}

func OptionBoolDecode() string {
	o := C.option_bool_decode(C.CString("01"))
	return C.GoString(o)
}
