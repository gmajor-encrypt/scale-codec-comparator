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

func BoolDecode() bool {
	o := C.bool_decode(C.CString("01"))
	output := (C.bool)(o)
	return bool(output) == true
}

func BoolEncode() string {
	o := C.bool_encode(C.bool(true))
	return C.GoString(o)
}

type ResultsType struct {
	Ok  uint
	Err string
}

func ResultEncode() string {
	o := C.results_encode(C.uint(2), C.CString("None"), C.CString("OK"))
	return C.GoString(o)
}

func ResultDecode() *ResultsType {
	o := C.results_decode(C.CString("0002000000"))
	result := (*C.struct_ResultsType)(o)
	return &ResultsType{Ok: uint(result.ok), Err: C.GoString(result.err)}
}

type CodecStruct struct {
	Data  int
	Other uint8
}

func StructDecode() *CodecStruct {
	o := C.data_struct_decode(C.CString("0a00000001"))
	result := (*C.struct_CodecStruct)(o)
	return &CodecStruct{Data: int(result.data), Other: uint8(result.other)}
}

func StructEncode() string {
	var s C.struct_CodecStruct
	s.data = 10
	s.other = 1
	o := C.data_struct_encode(&s)
	return C.GoString(o)
}

type EnumStruct struct {
	A uint
	B uint
	C uint
}

func EnumDecode() *EnumStruct {
	o := C.data_enum_decode(C.CString("0001000000"))
	result := (*C.struct_EnumStruct)(o)
	return &EnumStruct{A: uint(result.a), B: uint(result.b), C: uint(result.c)}

}

func EnumEncode() string {
	var s C.struct_EnumStruct
	s.a = 1
	o := C.data_enum_encode(&s)
	return C.GoString(o)
}

func StringEncode(s string) string {
	o := C.string_encode(C.CString(s))
	return C.GoString(o)
}

func StringDecode(raw string) string {
	o := C.string_decode(C.CString(raw))
	return C.GoString(o)
}
