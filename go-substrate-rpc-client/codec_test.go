package go_substrate_rpc_client

import (
	"bytes"
	"encoding/hex"
	"math/big"
	"strings"
	"testing"

	"github.com/centrifuge/go-substrate-rpc-client/scale"
	"github.com/centrifuge/go-substrate-rpc-client/types"
	"github.com/stretchr/testify/assert"
)

func hexToBytes(s string) []byte {
	s = strings.TrimPrefix(s, "0x")
	c := make([]byte, hex.DecodedLen(len(s)))
	_, _ = hex.Decode(c, []byte(s))
	return c
}

func bytesToHex(b []byte) string {
	c := make([]byte, hex.EncodedLen(len(b)))
	hex.Encode(c, b)
	return string(c)
}

func TestCompactU32(t *testing.T) {
	values := []uint{2, 65536, 0}
	for _, value := range values {
		var buffer = bytes.Buffer{}
		assert.NoError(t, scale.NewEncoder(&buffer).EncodeUintCompact(*big.NewInt(int64(value))))
		assert.Equal(t, CompactU32Encode(value), bytesToHex(buffer.Bytes()))
	}
	encodeValue := []string{"08", "02000400", "00"}
	for _, value := range encodeValue {
		var buffer = bytes.Buffer{}
		buffer.Write(hexToBytes(value))
		result, err := scale.NewDecoder(&buffer).DecodeUintCompact()
		assert.NoError(t, err)
		assert.Equal(t, CompactU32Decode(value), uint(result.Int64()))
	}
}

func TestOptionBool(t *testing.T) {
	var buffer = bytes.Buffer{}
	var boolValue scale.OptionBool
	buffer.Write(hexToBytes("01"))
	assert.NoError(t, scale.NewDecoder(&buffer).Decode(&boolValue))
	assert.Equal(t, scale.NewOptionBool(OptionBoolDecode("01")), boolValue)

	assert.NoError(t, scale.NewEncoder(&buffer).Encode(scale.NewOptionBool(true)))
	assert.Equal(t, OptionBoolEncode("true"), bytesToHex(buffer.Bytes()))
	buffer = bytes.Buffer{}
	assert.NoError(t, scale.NewEncoder(&buffer).Encode(scale.NewOptionBool(false)))
	assert.Equal(t, OptionBoolEncode("false"), bytesToHex(buffer.Bytes()))
	buffer = bytes.Buffer{}
	assert.NoError(t, scale.NewEncoder(&buffer).Encode(scale.NewOptionBoolEmpty()))
	assert.Equal(t, OptionBoolEncode("None"), bytesToHex(buffer.Bytes()))
}

func TestBool(t *testing.T) {
	values := []bool{true, false}
	for _, value := range values {
		var buffer = bytes.Buffer{}
		assert.NoError(t, scale.NewEncoder(&buffer).Encode(value))
		assert.Equal(t, BoolEncode(value), bytesToHex(buffer.Bytes()))
	}
	encodeValue := []string{"01", "00"}
	for _, value := range encodeValue {
		var buffer = bytes.Buffer{}
		buffer.Write(hexToBytes(value))
		var boolValue bool
		assert.NoError(t, scale.NewDecoder(&buffer).Decode(&boolValue))
		assert.Equal(t, BoolDecode(value), boolValue)
	}
}

func TestStruct(t *testing.T) {
	values := []CodecStruct{{Data: 10, Other: 1}, {Data: 1000000, Other: 16}, {Data: 0, Other: 0}}
	for _, value := range values {
		var buffer = bytes.Buffer{}
		assert.NoError(t, scale.NewEncoder(&buffer).Encode(value))
		assert.Equal(t, StructEncode(&value), bytesToHex(buffer.Bytes()))
	}
	encodeValue := []string{"0a00000001", "40420f0016"}
	for _, value := range encodeValue {
		var buffer = bytes.Buffer{}
		buffer.Write(hexToBytes(value))
		var structValue CodecStruct
		assert.NoError(t, scale.NewDecoder(&buffer).Decode(&structValue))
		assert.Equal(t, StructDecode(value), &structValue)
	}
}

type ScaleEnumStruct struct {
	IsA bool
	A   uint32
	IsB bool
	B   uint32
	IsC bool
	C   uint32
}

func (m *ScaleEnumStruct) Decode(decoder scale.Decoder) error {
	b, err := decoder.ReadOneByte()

	if err != nil {
		return err
	}

	if b == 0 {
		m.IsA = true
		err = decoder.Decode(&m.A)
	} else if b == 1 {
		m.IsB = true
		err = decoder.Decode(&m.B)
	} else if b == 2 {
		m.IsC = true
		err = decoder.Decode(&m.C)
	}

	if err != nil {
		return err
	}

	return nil
}

func (m ScaleEnumStruct) Encode(encoder scale.Encoder) error {
	var err1, err2 error
	if m.IsA {
		err1 = encoder.PushByte(0)
		err2 = encoder.Encode(m.A)
	} else if m.IsB {
		err1 = encoder.PushByte(1)
		err2 = encoder.Encode(m.B)
	} else if m.IsC {
		err1 = encoder.PushByte(2)
		err2 = encoder.Encode(m.C)
	}
	if err1 != nil {
		return err1
	}
	if err2 != nil {
		return err2
	}

	return nil
}

func TestEnum(t *testing.T) {

	value, err := types.EncodeToHexString(&ScaleEnumStruct{IsA: true, A: 1})
	assert.NoError(t, err)
	assert.Equal(t, EnumEncode(&EnumStruct{A: 1}), strings.TrimPrefix(value, "0x"))

	value, err = types.EncodeToHexString(&ScaleEnumStruct{IsB: true, B: 899999})
	assert.NoError(t, err)
	assert.Equal(t, EnumEncode(&EnumStruct{B: 899999}), strings.TrimPrefix(value, "0x"))

	value, err = types.EncodeToHexString(&ScaleEnumStruct{IsC: true, C: 10000000})
	assert.NoError(t, err)
	assert.Equal(t, EnumEncode(&EnumStruct{C: 10000000}), strings.TrimPrefix(value, "0x"))

	var decodeValue ScaleEnumStruct
	_ = types.DecodeFromHexString("0x0001000000", &decodeValue)
	assert.EqualValues(t, EnumDecode("0001000000").A, decodeValue.A)

	_ = types.DecodeFromHexString("0x019fbb0d00", &decodeValue)
	assert.EqualValues(t, EnumDecode("019fbb0d00").B, decodeValue.B)
}

func TestString(t *testing.T) {
	values := []string{
		"Hamlet",
		"Война и мир",
		"三国演义",
		"أَلْف لَيْلَة وَلَيْلَة‎"}
	for _, value := range values {
		var buffer = bytes.Buffer{}
		assert.NoError(t, scale.NewEncoder(&buffer).Encode(value))
		assert.Equal(t, StringEncode(value), bytesToHex(buffer.Bytes()))
	}
	values = []string{
		"1848616d6c6574",
		"30e4b889e59bbde6bc94e4b989",
		"50d092d0bed0b9d0bdd0b020d0b820d0bcd0b8d180"}
	for _, value := range values {
		var buffer = bytes.Buffer{}
		buffer.Write(hexToBytes(value))
		var target string
		assert.NoError(t, scale.NewDecoder(&buffer).Decode(&target))
		assert.Equal(t, StringDecode(value), target)
	}
}

func TestFixedU32(t *testing.T) {
	values := [][6]uint32{
		{1, 2, 3, 4, 5, 6},
		{666, 666, 66, 1, 2, 3},
	}
	for _, value := range values {
		var buffer = bytes.Buffer{}
		assert.NoError(t, scale.NewEncoder(&buffer).Encode(value))
		assert.Equal(t, FixU32Encode(ArrayU32ToUintArr(value)), bytesToHex(buffer.Bytes()))
	}

	encodeValues := []string{
		"010000000200000003000000040000000500000006000000",
		"9a0200009a02000042000000010000000200000003000000"}
	for _, value := range encodeValues {
		var buffer = bytes.Buffer{}
		buffer.Write(hexToBytes(value))
		var target [6]uint32
		assert.NoError(t, scale.NewDecoder(&buffer).Decode(&target))
		assert.EqualValues(t, FixU32Decode(value), ArrayU32ToUintSlice(target))
	}
}

func InterfaceArrToUintArr(v []interface{}) []uint {
	var uintArr []uint
	for _, v := range v {
		uintArr = append(uintArr, uint(v.(uint32)))
	}
	return uintArr
}

func ArrayU32ToUintArr(v [6]uint32) []uint32 {
	var uintArr []uint32
	for _, v := range v {
		uintArr = append(uintArr, v)
	}
	return uintArr
}

func ArrayU32ToUintSlice(v [6]uint32) []uint {
	var uintArr []uint
	for _, v := range v {
		uintArr = append(uintArr, uint(v))
	}
	return uintArr
}

func SliceU32ToUintSlice(v []uint32) []uint {
	var uintArr []uint
	for _, v := range v {
		uintArr = append(uintArr, uint(v))
	}
	return uintArr
}

//
func TestVecU32(t *testing.T) {

	values := [][]uint32{
		{1, 2, 3, 4, 5, 6},
		{666, 666, 66, 1, 2, 3},
	}
	for _, value := range values {
		var buffer = bytes.Buffer{}
		assert.NoError(t, scale.NewEncoder(&buffer).Encode(value))
		assert.Equal(t, VecU32Encode(value), bytesToHex(buffer.Bytes()))
	}

	encodeValues := []string{
		"18010000000200000003000000040000000500000006000000",
		"18030000000400000005000000060000000500000006000000"}
	for _, value := range encodeValues {
		var buffer = bytes.Buffer{}
		buffer.Write(hexToBytes(value))
		var target []uint32
		assert.NoError(t, scale.NewDecoder(&buffer).Decode(&target))
		assert.EqualValues(t, VecU32Decode(value), SliceU32ToUintSlice(target))
	}
}

func TestTupleU32U32(t *testing.T) {
	values := []TupleType{{A: 10, B: 1}, {A: 10111, B: 12412}, {B: 999999, A: 9999999}}
	for _, value := range values {
		var buffer = bytes.Buffer{}
		assert.NoError(t, scale.NewEncoder(&buffer).Encode(value))
		assert.Equal(t, TupleEncode(&value), bytesToHex(buffer.Bytes()))
	}
	encodeValue := []string{"0a00000001000000", "a0860100a0860100"}
	for _, value := range encodeValue {
		var buffer = bytes.Buffer{}
		buffer.Write(hexToBytes(value))
		var structValue TupleType
		assert.NoError(t, scale.NewDecoder(&buffer).Decode(&structValue))
		assert.Equal(t, TupleDecode(value), &structValue)
	}
}
