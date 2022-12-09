let ffi = require('ffi-napi');
let path = require('path');
const ref = require('ref-napi')
const RefStruct = require('ref-struct-di')(ref);
const ArrayType = require('ref-array-di')(ref);
import {bool, compact, Enum, Option, Result, str, Struct, Tuple, u32, u8, Vector,} from "scale-ts"


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
        expect(tohex(compact.enc(2))).toEqual(libm.compact_u32_encode(2));
        expect(tohex(compact.enc(0))).toEqual(libm.compact_u32_encode(0));
        expect(tohex(compact.enc(65536))).toEqual(libm.compact_u32_encode(65536));
    });
    it('decode compact<u32>', (): void => {
        expect(Number(compact.dec("08"))).toEqual(libm.compact_u32_decode("08"));
        expect(Number(compact.dec("00"))).toEqual(libm.compact_u32_decode("00"));
        expect(Number(compact.dec("02000400"))).toEqual(libm.compact_u32_decode("02000400"));
    });
    // //
    const optionalBool = Option(bool)
    it('encodes option<bool>', (): void => {
        expect(tohex(optionalBool.enc(undefined))).toEqual(libm.option_bool_encode("NONE"));
        expect(tohex(optionalBool.enc(true))).toEqual(libm.option_bool_encode("true"));
        expect(tohex(optionalBool.enc(false))).toEqual(libm.option_bool_encode("false"));
    });
    it('decode option<bool>', (): void => {
        expect(optionalBool.dec("01")).toEqual(libm.option_bool_decode("01") === 'true');
        expect(optionalBool.dec("00")).toEqual(libm.option_bool_decode("00") == "None" ? undefined : "true");
    });
    it('encodes bool', (): void => {
        expect(tohex(bool.enc(true))).toEqual(libm.bool_encode(true));
        expect(tohex(bool.enc(false))).toEqual(libm.bool_encode(false));
    });
    it('decode bool', (): void => {
        expect(bool.dec("01")).toEqual(libm.bool_decode("01"));
        expect(bool.dec("00")).toEqual(libm.bool_decode("00"));
    });
    // //
    const ResultU32ErrEncode = Result(u32, str)
    it('encode result<u32,string>', (): void => {
        const st = new resultsType;
        st.ok = 2;
        st.err = "";
        expect(tohex(ResultU32ErrEncode.enc({success: true, value: 2}))).toEqual(libm.results_encode(st.ref()));

        const stWithErr = new resultsType;
        stWithErr.ok = 0;
        stWithErr.err = "err";
        expect(tohex(ResultU32ErrEncode.enc({
            success: false,
            value: "err"
        }))).toEqual(libm.results_encode(stWithErr.ref()));
    });
    it('decode result<u32,string>', (): void => {
        let result = libm.results_decode("0002000000").deref().toJSON()
        delete result["err"]
        expect(ResultU32ErrEncode.dec("0002000000").value).toEqual(result["ok"]);
        result = libm.results_decode("010c657272").deref().toJSON()
        delete result["ok"]
        expect(ResultU32ErrEncode.dec("010c657272").value).toEqual(result["err"]);
    });
    // //
    // //
    const PStruct = Struct({data: u32, other: u8})
    it('encode struct', (): void => {
        const st = new CodecStruct;
        st.data = 10;
        st.other = 1;
        expect(tohex(PStruct.enc({
            data: 10,
            other: 1
        }))).toEqual(libm.data_struct_encode(st.ref()));
        st.data = 100;
        st.other = 15;
        expect(tohex(PStruct.enc({
            data: 100,
            other: 15
        }))).toEqual(libm.data_struct_encode(st.ref()));
    });
    it('decode struct', (): void => {
        expect((PStruct.dec(toU8a("0a00000001")))).toEqual(libm.data_struct_decode("0a00000001").deref().toJSON());
        expect((PStruct.dec(toU8a("0a00000002")))).toEqual(libm.data_struct_decode("0a00000002").deref().toJSON());
    });

    const PEnum = Enum({a: u32, b: u32, c: u32})
    it('encode enum', (): void => {
        const st = new EnumStruct;
        st.a = 1;
        expect(tohex(PEnum.enc({tag: "a", value: 1}))).toEqual(libm.data_enum_encode(st.ref()));
        const st2 = new EnumStruct;
        st2.b = 32;
        expect(tohex(PEnum.enc({tag: "b", value: 32}))).toEqual(libm.data_enum_encode(st2.ref()));

    });
    it('decode enum', (): void => {
        let value = libm.data_enum_decode("0001000000").deref().toJSON()
        delete value["b"]
        delete value["c"]

        expect(((PEnum.dec(toU8a("0001000000")))).value).toEqual(value["a"]);
        value = libm.data_enum_decode("0101000000").deref().toJSON()
        delete value["a"]
        delete value["c"]
        expect(((PEnum.dec(toU8a("0101000000")))).value).toEqual(value["b"]);
    });


    const PTuple = Tuple(u32, u32)
    it('encode tuple(u32,u32)', (): void => {
        const st = new TupleType;
        st.a = 10;
        st.b = 1;
        expect(tohex(PTuple.enc([10, 1]))).toEqual(libm.tuple_u32u32_encode(st.ref()));
        st.b = 86400
        st.a = 86400
        expect(tohex(PTuple.enc([86400, 86400]))).toEqual(libm.tuple_u32u32_encode(st.ref()));
    });

    it('decode tuple(u32,u32)', (): void => {
        let value = libm.tuple_u32u32_decode("0a00000001000000").deref().toJSON()
        expect((PTuple.dec(toU8a("0a00000001000000")))).toEqual([value["a"], value["b"]]);
        value = libm.tuple_u32u32_decode("0a00000005000000").deref().toJSON()
        expect((PTuple.dec(toU8a("0a00000005000000")))).toEqual([value["a"], value["b"]]);
    });

    it('encodes string', (): void => {
        expect(tohex(str.enc("Hamlet"))).toEqual(libm.string_encode("Hamlet"));
        expect(tohex(str.enc("Война и мир"))).toEqual(libm.string_encode("Война и мир"));
        expect(tohex(str.enc("三国演义"))).toEqual(libm.string_encode("三国演义"));
    });
    it('decode string', (): void => {
        expect(str.dec(toU8a("1848616d6c6574"))).toEqual(libm.string_decode("1848616d6c6574"));
        expect(str.dec(toU8a("30e4b889e59bbde6bc94e4b989"))).toEqual(libm.string_decode("30e4b889e59bbde6bc94e4b989"));
        expect(str.dec(toU8a("50d092d0bed0b9d0bdd0b020d0b820d0bcd0b8d180"))).toEqual(libm.string_decode("50d092d0bed0b9d0bdd0b020d0b820d0bcd0b8d180"));
    });
    //
    const VecFixedU32 = Vector(u32, 6)
    it('encode [u32;6]', (): void => {
        const IntArray = ArrayType('uint32');
        // @ts-ignore
        let array = new IntArray([1, 2, 3, 4, 5, 6]);
        expect(tohex(VecFixedU32.enc([1, 2, 3, 4, 5, 6]))).toEqual(libm.fixU32_encode(array.buffer, 6));
        array = new IntArray([0, 0, 0, 0, 0, 0]);
        expect(tohex(VecFixedU32.enc([0, 0, 0, 0, 0, 0]))).toEqual(libm.fixU32_encode(array.buffer, 6));
    });
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
            VecFixedU32.dec(toU8a("010000000200000003000000040000000500000006000000"))
        ).toEqual(values);
    });

    const VecU32 = Vector(u32)
    it('encode vec<u32>', (): void => {
        const IntArray = ArrayType('uint32');
        let array = new IntArray([1, 2, 3, 4, 5, 6]);
        expect(tohex(VecU32.enc([1, 2, 3, 4, 5, 6]))).toEqual(libm.vec_u32_encode(array.buffer, 6));
        array = new IntArray([0, 0, 0, 0, 0, 0]);
        expect(tohex(VecU32.enc([0, 0, 0, 0, 0, 0]))).toEqual(libm.vec_u32_encode(array.buffer, 6));
    });
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
        expect(VecU32.dec(toU8a("18010000000200000003000000040000000500000006000000"))).toEqual(values);
    });

})