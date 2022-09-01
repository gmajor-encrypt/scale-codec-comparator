package scale_go

/*
// #cgo linux LDFLAGS: -ldl -Wl,-rpath,$ORIGIN
// #cgo darwin LDFLAGS: -ldl -Wl,-undefined,dynamic_lookup
#cgo LDFLAGS: -L${SRCDIR} -lscale_ffi
#include <stdlib.h>
#include "../src/scale_ffi.h"
*/
import (
	"C"
)
import (
	"reflect"
	"unsafe"
)

func CompactU32Encode(input uint) string {
	o := C.compact_u32_encode(C.uint(input))
	return C.GoString(o)
}

func CompactU32Decode(input string) uint {
	o := C.compact_u32_decode(C.CString(input))
	output := (C.uint)(o)
	return uint(output)
}

func OptionBoolEncode(input string) string {
	o := C.option_bool_encode(C.CString(input))
	return C.GoString(o)
}

func OptionBoolDecode(input string) bool {
	o := C.option_bool_decode(C.CString(input))
	return C.GoString(o) == "true"
}

func BoolDecode(input string) bool {
	o := C.bool_decode(C.CString(input))
	output := (C.bool)(o)
	return bool(output) == true
}

func BoolEncode(input bool) string {
	o := C.bool_encode(C.bool(input))
	return C.GoString(o)
}

type ResultsType struct {
	Ok  uint
	Err string
}

func ResultEncode(data uint) string {
	o := C.results_encode(C.uint(data), C.CString("None"), C.CString("OK"))
	return C.GoString(o)
}

func ResultDecode(input string) *ResultsType {
	o := C.results_decode(C.CString(input))
	result := (*C.struct_ResultsType)(o)
	return &ResultsType{Ok: uint(result.ok), Err: C.GoString(result.err)}
}

type CodecStruct struct {
	Data  int
	Other uint8
}

func StructDecode(input string) *CodecStruct {
	o := C.data_struct_decode(C.CString(input))
	result := (*C.struct_CodecStruct)(o)
	return &CodecStruct{Data: int(result.data), Other: uint8(result.other)}
}

func StructEncode(input *CodecStruct) string {
	var s C.struct_CodecStruct
	s.data = C.uint(input.Data)
	s.other = C.uchar(input.Other)
	o := C.data_struct_encode(&s)
	return C.GoString(o)
}

type EnumStruct struct {
	A uint
	B uint
	C uint
}

func EnumDecode(input string) *EnumStruct {
	o := C.data_enum_decode(C.CString(input))
	result := (*C.struct_EnumStruct)(o)
	return &EnumStruct{A: uint(result.a), B: uint(result.b), C: uint(result.c)}

}

func EnumEncode(input *EnumStruct) string {
	var s C.struct_EnumStruct
	s.a = C.uint(input.A)
	s.b = C.uint(input.B)
	s.c = C.uint(input.C)
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

func FixU32Encode(input [6]uint32) string {
	o := C.fixU32_encode((*C.uint)(unsafe.Pointer(&input[0])), C.size_t(len(input)))
	return C.GoString(o)
}

func FixU32Decode(raw string) []uint {
	o := C.fixU32_decode(C.CString(raw))
	first := (*C.uint)(o)
	var arr []uint
	for _, v := range carray2slice(first, 6) {
		arr = append(arr, uint(v))
	}
	return arr
}

func VecU32Encode(input []uint32) string {
	o := C.vec_u32_encode((*C.uint)(unsafe.Pointer(&input[0])), C.uint(len(input)))
	return C.GoString(o)
}

func VecU32Decode(raw string) []uint {
	o := C.vec_u32_decode(C.CString(raw))
	first := (*C.uint)(o)
	var arr []uint
	for _, v := range carray2slice(first, 6) {
		arr = append(arr, uint(v))
	}
	return arr
}

func carray2slice(array *C.uint, len int) []C.uint {
	var list []C.uint
	sliceHeader := (*reflect.SliceHeader)(unsafe.Pointer(&list))
	sliceHeader.Cap = len
	sliceHeader.Len = len
	sliceHeader.Data = uintptr(unsafe.Pointer(array))
	return list
}

type TupleType struct {
	A uint
	B uint
}

func TupleDecode(input string) *TupleType {
	o := C.tuple_u32u32_decode(C.CString(input))
	result := (*C.struct_TupleType)(o)
	return &TupleType{A: uint(result.a), B: uint(result.b)}
}

func TupleEncode(input *TupleType) string {
	var s C.struct_TupleType
	s.a = C.uint(input.A)
	s.b = C.uint(input.B)
	o := C.tuple_u32u32_encode(&s)
	return C.GoString(o)
}
