package scale_go

import (
	"testing"

	"github.com/itering/scale.go/source"
	"github.com/itering/scale.go/types"
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

	assert.Equal(t, "0002000000", ResultEncode(2))
	assert.Equal(t, &ResultsType{Ok: uint(2), Err: ""}, ResultDecode("0002000000"))

	assert.Equal(t, "0a00000001", StructEncode(&CodecStruct{Data: 10, Other: 1}))
	assert.Equal(t, &CodecStruct{Data: 10, Other: 1}, StructDecode("0a00000001"))

	assert.Equal(t, "0001000000", EnumEncode(&EnumStruct{A: 1}))
	assert.Equal(t, &EnumStruct{A: 1}, EnumDecode("0001000000"))

	assert.Equal(t, "1848616d6c6574", StringEncode("Hamlet"))
	assert.Equal(t, "Hamlet", StringDecode("1848616d6c6574"))

	assert.Equal(t, "010000000200000003000000040000000500000006000000", FixU32Encode([6]uint32{1, 2, 3, 4, 5, 6}))
	assert.Equal(t, []uint{1, 2, 3, 4, 5, 6}, FixU32Decode("010000000200000003000000040000000500000006000000"))

	assert.Equal(t, "18010000000200000003000000040000000500000006000000", VecU32Encode([]uint32{1, 2, 3, 4, 5, 6}))
	assert.Equal(t, []uint{1, 2, 3, 4, 5, 6}, VecU32Decode("18010000000200000003000000040000000500000006000000"))

	assert.Equal(t, &TupleType{A: 10, B: 1}, TupleDecode("0a00000001000000"))
	assert.Equal(t, "0a00000001000000", TupleEncode(&TupleType{A: 10, B: 1}))
}


func TestCompactU32(t *testing.T) {
	m := types.ScaleDecoder{}
	m.Init(types.ScaleBytes{Data: utiles.HexToBytes("08")}, nil)
	assert.Equal(t, CompactU32Decode("08"), uint(m.ProcessAndUpdateData("Compact<u32>").(int)))
	assert.Equal(t, CompactU32Encode(2), types.Encode("Compact<u32>", 2))
}

func TestOptionBool(t *testing.T) {
	m := types.ScaleDecoder{}
	m.Init(types.ScaleBytes{Data: utiles.HexToBytes("01")}, nil)
	assert.EqualValues(t, OptionBoolDecode("01"), m.ProcessAndUpdateData("Option<bool>"))
	assert.Equal(t, OptionBoolEncode("01"), types.Encode("Option<bool>", true))
}

func TestBool(t *testing.T) {
	m := types.ScaleDecoder{}
	m.Init(types.ScaleBytes{Data: utiles.HexToBytes("01")}, nil)
	assert.EqualValues(t, OptionBoolDecode("01"), m.ProcessAndUpdateData("bool"))
	assert.Equal(t, OptionBoolEncode("true"), types.Encode("bool", true))
}

func TestResultsU32Err(t *testing.T) {
	m := types.ScaleDecoder{}
	m.Init(types.ScaleBytes{Data: utiles.HexToBytes("0002000000")}, nil)
	goResultValue := m.ProcessAndUpdateData("results<u32,string>").(map[string]interface{})
	ffiValue := ResultDecode("0002000000")
	assert.EqualValues(t, ffiValue.Ok, goResultValue["Ok"].(uint32))
	assert.Equal(t, ResultEncode(2), types.Encode("results<u32,string>", 2))
}

func TestStruct(t *testing.T) {
	m := types.ScaleDecoder{}
	types.RuntimeType{}.Reg()
	types.RegCustomTypes(source.LoadTypeRegistry([]byte(`{"t": {"type": "struct","type_mapping": [["Data","u32"],["Other","u8"]]}}`)))
	m.Init(types.ScaleBytes{Data: utiles.HexToBytes("0a00000001")}, nil)
	goStructValue := m.ProcessAndUpdateData("t").(map[string]interface{})
	ffiValue := StructDecode("0a00000001")
	assert.EqualValues(t, ffiValue.Data, goStructValue["Data"].(uint32))
	assert.EqualValues(t, ffiValue.Other, goStructValue["Other"].(int))
	assert.Equal(t, StructEncode(&CodecStruct{Data: 10, Other: 1}), types.Encode("t", map[string]interface{}{"Data": 10, "Other": 1}))
}

func TestEnum(t *testing.T) {
	m := types.ScaleDecoder{}
	types.RuntimeType{}.Reg()
	types.RegCustomTypes(source.LoadTypeRegistry([]byte(`{"te": {"type": "enum","type_mapping": [["A","u32"],["B","u32"],["C","u32"]]}}`)))
	m.Init(types.ScaleBytes{Data: utiles.HexToBytes("0001000000")}, nil)
	goStructValue := m.ProcessAndUpdateData("te").(map[string]interface{})
	ffiValue := EnumDecode("0001000000")
	assert.EqualValues(t, ffiValue.A, goStructValue["A"].(uint32))
	assert.Equal(t, EnumEncode(&EnumStruct{A: 1}), types.Encode("te", map[string]interface{}{"A": 1}))
}

func TestString(t *testing.T) {
	m := types.ScaleDecoder{}
	m.Init(types.ScaleBytes{Data: utiles.HexToBytes("1848616d6c6574")}, nil)
	assert.Equal(t, StringDecode("1848616d6c6574"), m.ProcessAndUpdateData("String").(string))
	assert.Equal(t, StringEncode("Hamlet"), types.Encode("String", "Hamlet"))
}

func TestFixedU32(t *testing.T) {
	m := types.ScaleDecoder{}
	m.Init(types.ScaleBytes{Data: utiles.HexToBytes("010000000200000003000000040000000500000006000000")}, nil)
	var uintArr []uint
	for _, v := range m.ProcessAndUpdateData("[u32; 6]").([]interface{}) {
		uintArr = append(uintArr, uint(v.(uint32)))
	}
	assert.EqualValues(t, FixU32Decode("010000000200000003000000040000000500000006000000"), uintArr)
	assert.Equal(t, FixU32Encode([6]uint32{1, 2, 3, 4, 5, 6}), types.Encode("[u32; 6]", []uint{1, 2, 3, 4, 5, 6}))
}

func TestVecU32(t *testing.T) {
	m := types.ScaleDecoder{}
	m.Init(types.ScaleBytes{Data: utiles.HexToBytes("18010000000200000003000000040000000500000006000000")}, nil)
	var uintArr []uint
	for _, v := range m.ProcessAndUpdateData("Vec<u32>").([]interface{}) {
		uintArr = append(uintArr, uint(v.(uint32)))
	}
	assert.EqualValues(t, VecU32Decode("18010000000200000003000000040000000500000006000000"), uintArr)
	assert.Equal(t, VecU32Encode([]uint32{1, 2, 3, 4, 5, 6}), types.Encode("Vec<u32>", []uint{1, 2, 3, 4, 5, 6}))
}

func TestTupleU32U32(t *testing.T) {
	m := types.ScaleDecoder{}
	m.Init(types.ScaleBytes{Data: utiles.HexToBytes("0a00000001000000")}, nil)
	tupleValue := TupleDecode("0a00000001000000")
	goTupleDecodeValue := m.ProcessAndUpdateData("(u32,u32)")
	assert.EqualValues(t, tupleValue.A, goTupleDecodeValue.(map[string]interface{})["col1"])
	assert.EqualValues(t, tupleValue.B, goTupleDecodeValue.(map[string]interface{})["col2"])
	assert.Equal(t, TupleEncode(&TupleType{A: 10, B: 1}), types.Encode("(u32,u32)", map[string]interface{}{"col1": 10, "col2": 1}))
}
