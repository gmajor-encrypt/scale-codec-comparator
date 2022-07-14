package scale_go

/*
#cgo LDFLAGS: -L../lib -lscale_ffi
#include <stdlib.h>
#include "../src/scale_ffi.h"
*/
import "C"
import (
	"fmt"
)

func test() {
	s := "Go say: Hello Rust"
	input := C.CString(s)
	o := C.rustdemo(input)
	output := C.GoString(o)
	fmt.Println("xxc", output)
}

func testCompactEncode() {
	o := C.compactU32encode(C.uint(2))
	output := C.GoString(o)
	fmt.Println("xxc", output)
}

func testCompactDecode() {
	o := C.compactU32decode(C.CString("08"))
	output := (C.uint)(o)
	fmt.Println("xxc", int(output))
}
