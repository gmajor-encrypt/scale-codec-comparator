package scale_go

import (
	"testing"

	"github.com/itering/scale.go/source"
	"github.com/itering/scale.go/types"
	"github.com/itering/scale.go/types/scaleBytes"
	"github.com/itering/scale.go/utiles"
	"github.com/stretchr/testify/assert"
)

func TestFFICodec(t *testing.T) {
	assert.Equal(t, "08", CompactU32Encode(2))
	assert.Equal(t, uint(2), CompactU32Decode("08"))

	assert.Equal(t, "00", OptionBoolEncode("NONE"))
	assert.Equal(t, true, OptionBoolDecode("01"))

	assert.Equal(t, true, BoolDecode("01"))
	assert.Equal(t, "01", BoolEncode(true))

	assert.Equal(t, "0002000000", ResultEncode(2, ""))
	assert.Equal(t, &ResultsType{Ok: uint(2), Err: ""}, ResultDecode("0002000000"))

	assert.Equal(t, "010c657272", ResultEncode(0, "err"))
	assert.Equal(t, &ResultsType{Ok: uint(0), Err: "err"}, ResultDecode("010c657272"))

	assert.Equal(t, "0a00000001", StructEncode(&CodecStruct{Data: 10, Other: 1}))
	assert.Equal(t, &CodecStruct{Data: 10, Other: 1}, StructDecode("0a00000001"))

	assert.Equal(t, "0001000000", EnumEncode(&EnumStruct{A: 1}))
	assert.Equal(t, &EnumStruct{A: 1}, EnumDecode("0001000000"))

	assert.Equal(t, "1848616d6c6574", StringEncode("Hamlet"))
	assert.Equal(t, "Hamlet", StringDecode("1848616d6c6574"))

	assert.Equal(t, "010000000200000003000000040000000500000006000000", FixU32Encode([]uint32{1, 2, 3, 4, 5, 6}))
	assert.Equal(t, []uint{1, 2, 3, 4, 5, 6}, FixU32Decode("010000000200000003000000040000000500000006000000"))

	assert.Equal(t, "18010000000200000003000000040000000500000006000000", VecU32Encode([]uint32{1, 2, 3, 4, 5, 6}))
	assert.Equal(t, []uint{1, 2, 3, 4, 5, 6}, VecU32Decode("18010000000200000003000000040000000500000006000000"))

	assert.Equal(t, &TupleType{A: 10, B: 1}, TupleDecode("0a00000001000000"))
	assert.Equal(t, "0a00000001000000", TupleEncode(&TupleType{A: 10, B: 1}))
}

func TestCompactU32(t *testing.T) {
	m := types.ScaleDecoder{}
	m.Init(scaleBytes.ScaleBytes{Data: utiles.HexToBytes("08")}, nil)
	assert.Equal(t, CompactU32Decode("08"), uint(m.ProcessAndUpdateData("Compact<u32>").(int)))
	m.Init(scaleBytes.ScaleBytes{Data: utiles.HexToBytes("02000400")}, nil)
	assert.Equal(t, CompactU32Decode("02000400"), uint(m.ProcessAndUpdateData("Compact<u32>").(int)))
	m.Init(scaleBytes.ScaleBytes{Data: utiles.HexToBytes("00")}, nil)
	assert.Equal(t, CompactU32Decode("00"), uint(m.ProcessAndUpdateData("Compact<u32>").(int)))
	assert.Equal(t, CompactU32Encode(2), types.Encode("Compact<u32>", 2))
	assert.Equal(t, CompactU32Encode(65536), types.Encode("Compact<u32>", 65536))
	assert.Equal(t, CompactU32Encode(0), types.Encode("Compact<u32>", 0))
}

func TestOptionBool(t *testing.T) {
	m := types.ScaleDecoder{}
	m.Init(scaleBytes.ScaleBytes{Data: utiles.HexToBytes("01")}, nil)
	assert.EqualValues(t, OptionBoolDecode("01"), m.ProcessAndUpdateData("Option<bool>"))
	m.Init(scaleBytes.ScaleBytes{Data: utiles.HexToBytes("02")}, nil)
	assert.EqualValues(t, OptionBoolDecode("02"), m.ProcessAndUpdateData("Option<bool>"))
	assert.Equal(t, OptionBoolEncode("true"), types.Encode("Option<bool>", true))
	assert.Equal(t, OptionBoolEncode("false"), types.Encode("Option<bool>", false))
	assert.Equal(t, OptionBoolEncode("None"), types.Encode("Option<bool>", ""))
}

func TestBool(t *testing.T) {
	m := types.ScaleDecoder{}
	m.Init(scaleBytes.ScaleBytes{Data: utiles.HexToBytes("01")}, nil)
	assert.EqualValues(t, BoolDecode("01"), m.ProcessAndUpdateData("bool"))
	m.Init(scaleBytes.ScaleBytes{Data: utiles.HexToBytes("00")}, nil)
	assert.EqualValues(t, BoolDecode("00"), m.ProcessAndUpdateData("bool"))
	assert.Equal(t, BoolEncode(true), types.Encode("bool", true))
	assert.Equal(t, BoolEncode(false), types.Encode("bool", false))
}

func TestResultsU32Err(t *testing.T) {
	m := types.ScaleDecoder{}
	m.Init(scaleBytes.ScaleBytes{Data: utiles.HexToBytes("0002000000")}, nil)
	ffiValue := ResultDecode("0002000000")
	// test with ok
	assert.EqualValues(t, ffiValue.Ok, m.ProcessAndUpdateData("Result<u32,string>").(map[string]interface{})["Ok"].(uint32))
	assert.Equal(t, ResultEncode(2, ""), types.Encode("Result<u32,string>", map[string]interface{}{"Ok": 2}))
	// test with err
	m.Init(scaleBytes.ScaleBytes{Data: utiles.HexToBytes("010c657272")}, nil)
	ffiValue = ResultDecode("010c657272")
	assert.EqualValues(t, ffiValue.Err, m.ProcessAndUpdateData("Result<u32,string>").(map[string]interface{})["Error"].(string))
	assert.Equal(t, ResultEncode(0, "err"), types.Encode("Result<u32,string>", map[string]interface{}{"Error": "err"}))
}

func TestStruct(t *testing.T) {
	m := types.ScaleDecoder{}
	types.Encode("bool", true)
	types.RegCustomTypes(source.LoadTypeRegistry([]byte(`{"t": {"type": "struct","type_mapping": [["Data","u32"],["Other","u8"]]}}`)))
	m.Init(scaleBytes.ScaleBytes{Data: utiles.HexToBytes("0a00000001")}, nil)
	goStructValue := m.ProcessAndUpdateData("t").(map[string]interface{})
	assert.EqualValues(t, StructDecode("0a00000001"), &CodecStruct{Other: uint8(goStructValue["Other"].(int)), Data: int(goStructValue["Data"].(uint32))})

	m.Init(scaleBytes.ScaleBytes{Data: utiles.HexToBytes("40420f0016")}, nil)
	goStructValue = m.ProcessAndUpdateData("t").(map[string]interface{})
	assert.EqualValues(t, StructDecode("40420f0016"), &CodecStruct{Other: uint8(goStructValue["Other"].(int)), Data: int(goStructValue["Data"].(uint32))})
	assert.Equal(t, StructEncode(&CodecStruct{Data: 10, Other: 1}), types.Encode("t", map[string]interface{}{"Data": 10, "Other": 1}))
	assert.Equal(t, StructEncode(&CodecStruct{Data: 1000000, Other: 16}), types.Encode("t", map[string]interface{}{"Data": 1000000, "Other": 16}))
	assert.Equal(t, StructEncode(&CodecStruct{Data: 0, Other: 0}), types.Encode("t", map[string]interface{}{"Data": 0, "Other": 0}))
}

func TestEnum(t *testing.T) {
	m := types.ScaleDecoder{}
	types.Encode("bool", true)
	types.RegCustomTypes(source.LoadTypeRegistry([]byte(`{"te": {"type": "enum","type_mapping": [["A","u32"],["B","u32"],["C","u32"]]}}`)))
	m.Init(scaleBytes.ScaleBytes{Data: utiles.HexToBytes("0001000000")}, nil)
	assert.EqualValues(t, EnumDecode("0001000000").A, m.ProcessAndUpdateData("te").(map[string]interface{})["A"].(uint32))

	m.Init(scaleBytes.ScaleBytes{Data: utiles.HexToBytes("019fbb0d00")}, nil)
	assert.EqualValues(t, EnumDecode("019fbb0d00").B, m.ProcessAndUpdateData("te").(map[string]interface{})["B"].(uint32))

	assert.Equal(t, EnumEncode(&EnumStruct{A: 1}), types.Encode("te", map[string]interface{}{"A": 1}))
	assert.Equal(t, EnumEncode(&EnumStruct{B: 899999}), types.Encode("te", map[string]interface{}{"B": 899999}))
	assert.Equal(t, EnumEncode(&EnumStruct{C: 10000000}), types.Encode("te", map[string]interface{}{"C": 10000000}))
}

func TestString(t *testing.T) {
	m := types.ScaleDecoder{}
	m.Init(scaleBytes.ScaleBytes{Data: utiles.HexToBytes("1848616d6c6574")}, nil)
	assert.Equal(t, StringDecode("1848616d6c6574"), m.ProcessAndUpdateData("String").(string))
	m.Init(scaleBytes.ScaleBytes{Data: utiles.HexToBytes("30e4b889e59bbde6bc94e4b989")}, nil)
	assert.Equal(t, StringDecode("30e4b889e59bbde6bc94e4b989"), string(utiles.HexToBytes(m.ProcessAndUpdateData("String").(string))))
	m.Init(scaleBytes.ScaleBytes{Data: utiles.HexToBytes("50d092d0bed0b9d0bdd0b020d0b820d0bcd0b8d180")}, nil)
	assert.Equal(t, StringDecode("50d092d0bed0b9d0bdd0b020d0b820d0bcd0b8d180"), string(utiles.HexToBytes(m.ProcessAndUpdateData("String").(string))))
	assert.Equal(t, StringEncode("Hamlet"), types.Encode("String", "Hamlet"))
	assert.Equal(t, StringEncode("Война и мир"), types.Encode("String", "Война и мир"))
	assert.Equal(t, StringEncode("三国演义"), types.Encode("String", "三国演义"))
}

func TestFixedU32(t *testing.T) {
	m := types.ScaleDecoder{}
	m.Init(scaleBytes.ScaleBytes{Data: utiles.HexToBytes("010000000200000003000000040000000500000006000000")}, nil)
	assert.EqualValues(t, FixU32Decode("010000000200000003000000040000000500000006000000"), InterfaceArrToUintArr(m.ProcessAndUpdateData("[u32; 6]").([]interface{})))
	m.Init(scaleBytes.ScaleBytes{Data: utiles.HexToBytes("030000000400000005000000060000000700000009500000")}, nil)
	assert.EqualValues(t, FixU32Decode("030000000400000005000000060000000700000009500000"), InterfaceArrToUintArr(m.ProcessAndUpdateData("[u32; 6]").([]interface{})))
	assert.Equal(t, FixU32Encode([]uint32{1, 2, 3, 4, 5, 6}), types.Encode("[u32; 6]", []int{1, 2, 3, 4, 5, 6}))
	assert.Equal(t, FixU32Encode([]uint32{666, 666, 66, 1, 2, 3}), types.Encode("[u32; 6]", []int{666, 666, 66, 1, 2, 3}))
}

func InterfaceArrToUintArr(v []interface{}) []uint {
	var uintArr []uint
	for _, v := range v {
		uintArr = append(uintArr, uint(v.(uint32)))
	}
	return uintArr
}

func TestVecU32(t *testing.T) {
	m := types.ScaleDecoder{}
	m.Init(scaleBytes.ScaleBytes{Data: utiles.HexToBytes("18010000000200000003000000040000000500000006000000")}, nil)
	assert.EqualValues(t, VecU32Decode("18010000000200000003000000040000000500000006000000"), InterfaceArrToUintArr(m.ProcessAndUpdateData("Vec<u32>").([]interface{})))
	m.Init(scaleBytes.ScaleBytes{Data: utiles.HexToBytes("18030000000400000005000000060000000500000006000000")}, nil)
	assert.EqualValues(t, VecU32Decode("18030000000400000005000000060000000500000006000000"), InterfaceArrToUintArr(m.ProcessAndUpdateData("Vec<u32>").([]interface{})))

	assert.Equal(t, VecU32Encode([]uint32{1, 2, 3, 4, 5, 6}), types.Encode("Vec<u32>", []uint32{1, 2, 3, 4, 5, 6}))
	assert.Equal(t, VecU32Encode([]uint32{123123, 212412, 3412, 0, 12, 6}), types.Encode("Vec<u32>", []uint32{123123, 212412, 3412, 0, 12, 6}))
}

func TestTupleU32U32(t *testing.T) {
	m := types.ScaleDecoder{}
	m.Init(scaleBytes.ScaleBytes{Data: utiles.HexToBytes("0a00000001000000")}, nil)
	goTupleDecodeValue := m.ProcessAndUpdateData("(u32,u32)")
	assert.EqualValues(t, TupleDecode("0a00000001000000"), &TupleType{A: uint(goTupleDecodeValue.(map[string]interface{})["col1"].(uint32)), B: uint(goTupleDecodeValue.(map[string]interface{})["col2"].(uint32))})

	m.Init(scaleBytes.ScaleBytes{Data: utiles.HexToBytes("a0860100a0860100")}, nil)
	goTupleDecodeValue = m.ProcessAndUpdateData("(u32,u32)")
	assert.EqualValues(t, TupleDecode("a0860100a0860100"), &TupleType{A: uint(goTupleDecodeValue.(map[string]interface{})["col1"].(uint32)), B: uint(goTupleDecodeValue.(map[string]interface{})["col2"].(uint32))})
	assert.Equal(t, TupleEncode(&TupleType{A: 10, B: 1}), types.Encode("(u32,u32)", map[string]interface{}{"col1": 10, "col2": 1}))
	assert.Equal(t, TupleEncode(&TupleType{A: 100000, B: 100000}), types.Encode("(u32,u32)", map[string]interface{}{"col1": 100000, "col2": 100000}))
}
