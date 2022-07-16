package scale_go

import (
	"testing"

	"github.com/stretchr/testify/assert"
)

func TestCodec(t *testing.T) {
	assert.Equal(t, "08", CompactU32Encode())
	assert.Equal(t, uint(2), CompactU32Decode())

	assert.Equal(t, "00", OptionBoolEncode())
	assert.Equal(t, "true", OptionBoolDecode())

	assert.Equal(t, true, BoolDecode())
	assert.Equal(t, "01", BoolEncode())

	assert.Equal(t, "0002000000", ResultEncode())
	assert.Equal(t, &ResultsType{Ok: uint(2), Err: ""}, ResultDecode())

	assert.Equal(t, "0a00000001", StructEncode())
	assert.Equal(t, &CodecStruct{Data: 10, Other: 1}, StructDecode())

	assert.Equal(t, "0001000000", EnumEncode())
	assert.Equal(t, &EnumStruct{A: 1}, EnumDecode())

	assert.Equal(t, "1848616d6c6574", StringEncode("Hamlet"))
	assert.Equal(t, "Hamlet", StringDecode("1848616d6c6574"))

	assert.Equal(t, "010000000200000003000000040000000500000006000000", FixU32Encode([6]uint32{1, 2, 3, 4, 5, 6}))
	assert.Equal(t, []uint{1, 2, 3, 4, 5, 6}, FixU32Decode("010000000200000003000000040000000500000006000000"))

	assert.Equal(t, "18010000000200000003000000040000000500000006000000", VecU32Encode([]uint32{1, 2, 3, 4, 5, 6}))
	assert.Equal(t, []uint{1, 2, 3, 4, 5, 6}, VecU32Decode("18010000000200000003000000040000000500000006000000"))

	assert.Equal(t, &TupleType{A: 10, B: 1}, TupleDecode())
	assert.Equal(t, "0a00000001000000", TupleEncode())
}
