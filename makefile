ifeq ($(shell uname),Darwin)
    EXT := dylib
else
    EXT := so
endif

all: target/debug/libscale_ffi.$(EXT)

target/debug/libscale_ffi.$(EXT): src/lib.rs Cargo.toml
	cargo build && mv target/debug/libscale_ffi.$(EXT) lib/libscale_ffi.$(EXT)

clean:
	rm -rf target

testScaleGo:
	cp lib/libscale_ffi.$(EXT) scale.go/libscale_ffi.$(EXT) && cd scale.go && go test -v ./...