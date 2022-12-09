let ffi = require('ffi-napi');
let path = require('path');
const ref = require('ref-napi')
const RefStruct = require('ref-struct-di')(ref);
const ArrayType = require('ref-array-di')(ref);
import {
    createArrayDecoder,
    createArrayEncoder,
    createEnumDecoder,
    createEnumEncoder,
    createResultDecoder,
    createResultEncoder,
    createStructDecoder,
    createStructEncoder,
    createTupleDecoder,
    createTupleEncoder,
    createVecDecoder,
    createVecEncoder,
    decodeBool,
    decodeCompact,
    decodeOptionBool,
    decodeStr,
    decodeU32,
    decodeU8,
    encodeBool,
    encodeCompact,
    encodeOptionBool,
    encodeStr,
    encodeU32,
    encodeU8,
    Enum,
    WalkerImpl
} from '@scale-codec/core'


let rootPath = process.env.FFI_PATH || path.resolve(path.dirname(path.dirname(__dirname)))

const resultsType = RefStruct({
    ok: ref.types.uint,
    err: 'string'
});

const CodecStruct = RefStruct({
    data: "uint",
    other: 'uint8'
});

const EnumStruct = RefStruct({
    a: "uint",
    b: 'uint',
    c: 'uint'
});

const TupleType = RefStruct({
    a: "uint",
    b: 'uint'
});

let libm = ffi.Library(rootPath + "/lib/libscale_ffi", {
    'compact_u32_encode': ['string', ['uint']],
    'compact_u32_decode': ['uint', ['string']],
    'option_bool_encode': ['string', ['string']],
    'option_bool_decode': ['string', ['string']],
    'bool_encode': ['string', [ffi.types.bool]],
    'bool_decode': [ffi.types.bool, ['string']],
    'results_encode': ['string', [ref.refType(resultsType)]],
    'results_decode': [ref.refType(resultsType), ['string']],
    'data_struct_encode': ['string', [ref.refType(CodecStruct)]],
    'data_struct_decode': [ref.refType(CodecStruct), ['string']],
    'data_enum_encode': ['string', [ref.refType(EnumStruct)]],
    'data_enum_decode': [ref.refType(EnumStruct), ['string']],
    'tuple_u32u32_encode': ['string', [ref.refType(TupleType)]],
    'tuple_u32u32_decode': [ref.refType(TupleType), ['string']],
    'string_encode': ['string', ['string']],
    'string_decode': ['string', ['string']],
    'fixU32_encode': ['string', [ref.refType("uint"), "uint"]],
    'fixU32_decode': [ref.refType(ArrayType('uint32')), ['string']],
    'vec_u32_encode': ['string', [ref.refType("uint"), "uint"]],
    'vec_u32_decode': [ref.refType(ArrayType('uint32')), ['string']],
});

function tohex(u8a) {
    return Buffer.from(u8a).toString('hex')
}

function toU8a(hexString) {
    return Uint8Array.from(Buffer.from(hexString, 'hex'));
}

// api.createType('Balance', 123);
describe('base ffi codec', (): void => {

    // Compact<U32>
    it('encodes compact<u32>', (): void => {
        expect(tohex(WalkerImpl.encode(2, encodeCompact))).toEqual(libm.compact_u32_encode(2));
        expect(tohex(WalkerImpl.encode(0, encodeCompact))).toEqual(libm.compact_u32_encode(0));
        expect(tohex(WalkerImpl.encode(65536, encodeCompact))).toEqual(libm.compact_u32_encode(65536));
    });
    it('decode compact<u32>', (): void => {
        expect(Number(WalkerImpl.decode(toU8a("08"), decodeCompact))).toEqual(libm.compact_u32_decode("08"));
        expect(Number(WalkerImpl.decode(toU8a("00"), decodeCompact))).toEqual(libm.compact_u32_decode("00"));
        expect(Number(WalkerImpl.decode(toU8a("02000400"), decodeCompact))).toEqual(libm.compact_u32_decode("02000400"));
    });
    //
    //
    it('encodes option<bool>', (): void => {
        expect(tohex(WalkerImpl.encode(Enum.variant('None'), encodeOptionBool))).toEqual(libm.option_bool_encode("NONE"));
        expect(tohex(WalkerImpl.encode(Enum.variant('Some', true), encodeOptionBool))).toEqual(libm.option_bool_encode("true"));
        expect(tohex(WalkerImpl.encode(Enum.variant('Some', false), encodeOptionBool))).toEqual(libm.option_bool_encode("false"));
    });
    it('decode option<bool>', (): void => {
        expect(WalkerImpl.decode(toU8a("01"), decodeOptionBool).value).toEqual(libm.option_bool_decode("01") === 'true');
        expect(WalkerImpl.decode(toU8a("00"), decodeOptionBool).tag).toEqual(libm.option_bool_decode("00") == "None" ? "None" : "true");
    });
    it('encodes bool', (): void => {
        expect(tohex(WalkerImpl.encode(true, encodeBool))).toEqual(libm.bool_encode(true));
        expect(tohex(WalkerImpl.encode(false, encodeBool))).toEqual(libm.bool_encode(false));
    });
    it('decode bool', (): void => {
        expect(WalkerImpl.decode(toU8a("01"), decodeBool)).toEqual(libm.bool_decode("01"));
        expect(WalkerImpl.decode(toU8a("00"), decodeBool)).toEqual(libm.bool_decode("00"));
    });
    //
    const ResultU32ErrEncode = createResultEncoder(encodeU32, encodeStr)
    it('encode result<u32,string>', (): void => {
        const st = new resultsType;
        st.ok = 2;
        st.err = "";
        expect(tohex(WalkerImpl.encode(Enum.variant('Ok', 2), ResultU32ErrEncode))).toEqual(libm.results_encode(st.ref()));

        const stWithErr = new resultsType;
        stWithErr.ok = 0;
        stWithErr.err = "err";
        expect(tohex(WalkerImpl.encode(Enum.variant('Err', "err"), ResultU32ErrEncode))).toEqual(libm.results_encode(stWithErr.ref()));
    });
    const ResultU32ErrDecode = createResultDecoder(decodeU32, decodeStr)
    it('decode result<u32,string>', (): void => {
        let result = libm.results_decode("0002000000").deref().toJSON()
        delete result["err"]
        expect((WalkerImpl.decode(toU8a("0002000000"), ResultU32ErrDecode)).value).toEqual(result["ok"]);
        result = libm.results_decode("010c657272").deref().toJSON()
        delete result["ok"]
        expect((WalkerImpl.decode(toU8a("010c657272"), ResultU32ErrDecode)).value).toEqual(result["err"]);
    });
    //
    //
    // const PStruct = Struct.with({data: U32, other: U8})
    type pStruct = {
        data: number,
        other: number
    }
    const PStructEncode = createStructEncoder<pStruct>([['data', encodeU32], ['other', encodeU8]])
    it('encode struct', (): void => {
        const st = new CodecStruct;
        st.data = 10;
        st.other = 1;
        expect(tohex(WalkerImpl.encode<pStruct>({
            data: 10,
            other: 1
        }, PStructEncode))).toEqual(libm.data_struct_encode(st.ref()));
        st.data = 100;
        st.other = 15;
        expect(tohex(WalkerImpl.encode<pStruct>({
            data: 100,
            other: 15
        }, PStructEncode))).toEqual(libm.data_struct_encode(st.ref()));
    });
    const PStructDecode = createStructDecoder<pStruct>([['data', decodeU32], ['other', decodeU8]])
    it('decode struct', (): void => {
        expect((WalkerImpl.decode(toU8a("0a00000001"), PStructDecode))).toEqual(libm.data_struct_decode("0a00000001").deref().toJSON());
        expect((WalkerImpl.decode(toU8a("0a00000002"), PStructDecode))).toEqual(libm.data_struct_decode("0a00000002").deref().toJSON());
    });
    // type PEnum = Enum<['a', number] | ['b', number] | ['c', number]>
    const PEnumEncoder = createEnumEncoder({a: [0, encodeU32], b: [1, encodeU32], c: [2, encodeU32]} as any)
    it('encode enum', (): void => {
        const st = new EnumStruct;
        st.a = 1;
        expect(tohex(WalkerImpl.encode(Enum.variant('a', 1), PEnumEncoder))).toEqual(libm.data_enum_encode(st.ref()));
        const st2 = new EnumStruct;
        st2.b = 32;
        expect((tohex(WalkerImpl.encode(Enum.variant('b', 32), PEnumEncoder)))).toEqual(libm.data_enum_encode(st2.ref()));

    });
    const PEnumDecoder = createEnumDecoder<Enum<['a', number] | ['b', number] | ['c', number]>>({
        0: ["a", decodeU32],
        1: ["b", decodeU32],
        2: ["c", decodeU32]
    } as any)
    it('decode enum', (): void => {
        let value = libm.data_enum_decode("0001000000").deref().toJSON()
        delete value["b"]
        delete value["c"]

        expect(((WalkerImpl.decode(toU8a("0001000000"), PEnumDecoder))).value).toEqual(value["a"]);
        value = libm.data_enum_decode("0101000000").deref().toJSON()
        delete value["a"]
        delete value["c"]
        expect(((WalkerImpl.decode(toU8a("0101000000"), PEnumDecoder))).value).toEqual(value["b"]);
    });
    //
    type SampleTuple = [number, number]
    const PTupleEncoder = createTupleEncoder<SampleTuple>([encodeU32, encodeU32])
    it('encode tuple(u32,u32)', (): void => {
        const st = new TupleType;
        st.a = 10;
        st.b = 1;
        expect(tohex(WalkerImpl.encode([10, 1], PTupleEncoder))).toEqual(libm.tuple_u32u32_encode(st.ref()));
        st.b = 86400
        st.a = 86400
        expect(tohex(WalkerImpl.encode([86400, 86400], PTupleEncoder))).toEqual(libm.tuple_u32u32_encode(st.ref()));
    });
    const PTupleDecoder = createTupleDecoder<SampleTuple>([decodeU32, decodeU32])
    it('decode tuple(u32,u32)', (): void => {
        let value = libm.tuple_u32u32_decode("0a00000001000000").deref().toJSON()
        expect((WalkerImpl.decode(toU8a("0a00000001000000"), PTupleDecoder))).toEqual([value["a"], value["b"]]);
        value = libm.tuple_u32u32_decode("0a00000005000000").deref().toJSON()
        expect((WalkerImpl.decode(toU8a("0a00000005000000"), PTupleDecoder))).toEqual([value["a"], value["b"]]);
    });

    it('encodes string', (): void => {
        expect(tohex(WalkerImpl.encode("Hamlet", encodeStr))).toEqual(libm.string_encode("Hamlet"));
        expect(tohex(WalkerImpl.encode("Война и мир", encodeStr))).toEqual(libm.string_encode("Война и мир"));
        expect(tohex(WalkerImpl.encode("三国演义", encodeStr))).toEqual(libm.string_encode("三国演义"));
    });
    it('decode string', (): void => {
        expect(WalkerImpl.decode(toU8a("1848616d6c6574"), decodeStr)).toEqual(libm.string_decode("1848616d6c6574"));
        expect(WalkerImpl.decode(toU8a("30e4b889e59bbde6bc94e4b989"), decodeStr)).toEqual(libm.string_decode("30e4b889e59bbde6bc94e4b989"));
        expect(WalkerImpl.decode(toU8a("50d092d0bed0b9d0bdd0b020d0b820d0bcd0b8d180"), decodeStr)).toEqual(libm.string_decode("50d092d0bed0b9d0bdd0b020d0b820d0bcd0b8d180"));
    });

    const VecFixedU32Encoder = createArrayEncoder(encodeU32, 6)
    it('encode [u32;6]', (): void => {
        const IntArray = ArrayType('uint32');
        // @ts-ignore
        let array = new IntArray([1, 2, 3, 4, 5, 6]);
        expect(tohex(WalkerImpl.encode([1, 2, 3, 4, 5, 6], VecFixedU32Encoder))).toEqual(libm.fixU32_encode(array.buffer, 6));
        array = new IntArray([0, 0, 0, 0, 0, 0]);
        expect(tohex(WalkerImpl.encode([0, 0, 0, 0, 0, 0], VecFixedU32Encoder))).toEqual(libm.fixU32_encode(array.buffer, 6));
    });
    const VecFixedU32Decoder = createArrayDecoder(decodeU32, 6)
    it('decode [u32;6]', (): void => {
        // const buf = Buffer.alloc(ref.sizeof.pointer);
        let value = libm.fixU32_decode("010000000200000003000000040000000500000006000000")
        let buf = Buffer.alloc(ref.sizeof.pointer);
        ref.writePointer(buf, 0, value);
        let uint32Size = ref.sizeof.uint
        buf = ref.readPointer(buf, 0, uint32Size * 6);
        const values = [];
        for (let i = 0; i < 6; i++) {
            const ptr = ref.get(buf, i * uint32Size, ref.types.uint);
            values.push(ptr);
        }
        expect(
            WalkerImpl.decode(toU8a("010000000200000003000000040000000500000006000000"), VecFixedU32Decoder)
        ).toEqual(values);
    });

    const VecU32Encoder = createVecEncoder(encodeU32)
    it('encode vec<u32>', (): void => {
        const IntArray = ArrayType('uint32');
        let array = new IntArray([1, 2, 3, 4, 5, 6]);
        expect(tohex(WalkerImpl.encode([1, 2, 3, 4, 5, 6], VecU32Encoder))).toEqual(libm.vec_u32_encode(array.buffer, 6));
        array = new IntArray([0, 0, 0, 0, 0, 0]);
        expect(tohex(WalkerImpl.encode([0, 0, 0, 0, 0, 0], VecU32Encoder))).toEqual(libm.vec_u32_encode(array.buffer, 6));
    });
    const VecU32Decoder = createVecDecoder(decodeU32)
    it('decode <u32>', (): void => {
        let value = libm.vec_u32_decode("18010000000200000003000000040000000500000006000000")
        let buf = Buffer.alloc(ref.sizeof.pointer);
        ref.writePointer(buf, 0, value);
        let uint32Size = ref.sizeof.uint
        buf = ref.readPointer(buf, 0, uint32Size * 6);
        const values = [];
        for (let i = 0; i < 6; i++) {
            const ptr = ref.get(buf, i * uint32Size, ref.types.uint);
            values.push(ptr);
        }
        expect(WalkerImpl.decode(toU8a("18010000000200000003000000040000000500000006000000"), VecU32Decoder)).toEqual(values);
    });

})