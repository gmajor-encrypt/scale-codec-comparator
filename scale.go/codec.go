package scale_go

/*
#cgo LDFLAGS: -L../lib -lscale_ffi
#include <stdlib.h>
#include "../src/scale_ffi.h"
*/
import "C"
import "fmt"

func test() {
	s := "Go say: Hello Rust"
	input := C.CString(s)
	o := C.rustdemo(input)
	output := C.GoString(o)
	fmt.Println("xxc", output)
}
