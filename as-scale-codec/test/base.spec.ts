let ffi = require('ffi-napi');
let path = require('path');
const util = require('util');
let fs = require('fs');
const ref = require('ref-napi')
const RefStruct = require('ref-struct-di')(ref);
const ArrayType = require('ref-array-di')(ref);
const loader = require("@assemblyscript/loader");

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

const WasmPath = rootPath + "/as-scale-codec-wasm/build/release.wasm";

const wasmModule = loader.instantiateSync(fs.readFileSync(WasmPath)).exports;

function wasmU8aStringToHex(u8aString) {
    const u8aArr = u8aString.split(",");
    const u8a = new Uint8Array(u8aArr.length);
    for (let i = 0; i < u8aArr.length; i++) {
        u8a[i] = u8aArr[i]
    }
    return tohex(u8a)
}


describe('base ffi codec', (): void => {

    // Compact<U32>
    it('compact<u32>', (): void => {
        expect(wasmU8aStringToHex(wasmModule.__getString(wasmModule.TestWithCompactU32(2)))).toEqual(libm.compact_u32_encode(2));
        expect(wasmU8aStringToHex(wasmModule.__getString(wasmModule.TestWithCompactU32(0)))).toEqual(libm.compact_u32_encode(0));
        expect(wasmU8aStringToHex(wasmModule.__getString(wasmModule.TestWithCompactU32(65536)))).toEqual(libm.compact_u32_encode(65536));
        expect(wasmU8aStringToHex(wasmModule.__getString(wasmModule.TestWithCompactU32(30800)))).toEqual(libm.compact_u32_encode(30800));
        expect(wasmU8aStringToHex(wasmModule.__getString(wasmModule.TestWithCompactU32(50000)))).toEqual(libm.compact_u32_encode(50000));
        expect(wasmU8aStringToHex(wasmModule.__getString(wasmModule.TestWithCompactU32(100000)))).toEqual(libm.compact_u32_encode(100000));
    });

    // Bool
    it('Bool', (): void => {
        expect((wasmU8aStringToHex(wasmModule.__getString(wasmModule.TestWithBool(true))))).toEqual(libm.bool_encode(true));
        expect((wasmU8aStringToHex(wasmModule.__getString(wasmModule.TestWithBool(false))))).toEqual(libm.bool_encode(false));
    });


    // String
    it('string', (): void => {
        expect(wasmModule.__getString(wasmModule.TestWithString(wasmModule.__newString("Hamlet")))).toEqual(libm.string_encode("Hamlet"));
        expect(wasmModule.__getString(wasmModule.TestWithString(wasmModule.__newString("Война и мир")))).toEqual(libm.string_encode("Война и мир"));
        expect(wasmModule.__getString(wasmModule.TestWithString(wasmModule.__newString("三国演义")))).toEqual(libm.string_encode("三国演义"));
    });

    // Option<bool>
    it('option<bool>', (): void => {
        expect((wasmU8aStringToHex(wasmModule.__getString(wasmModule.TestWithOptionBool())))).toEqual(libm.option_bool_encode("NONE"));
        expect((wasmU8aStringToHex(wasmModule.__getString(wasmModule.TestWithOptionBool())))).toEqual(libm.option_bool_encode("true"));
        expect((wasmU8aStringToHex(wasmModule.__getString(wasmModule.TestWithOptionBool())))).toEqual(libm.option_bool_encode("false"));
    });

    // Result<u32, string>
    it('result<u32,string>', (): void => {
        const st = new resultsType;
        st.ok = 2;
        st.err = "";
        expect(wasmU8aStringToHex(wasmModule.__getString(wasmModule.TestWithResultU32String()))).toEqual(libm.results_encode(st.ref()));

        const stWithErr = new resultsType;
        stWithErr.ok = 0;
        stWithErr.err = "err";
        expect(wasmU8aStringToHex(wasmModule.__getString(wasmModule.TestWithResultU32String()))).toEqual(libm.results_encode(stWithErr.ref()));
    });

    // Struct
    it('encode struct', (): void => {
        const st = new CodecStruct;
        st.data = 10;
        st.other = 1;
        expect(wasmU8aStringToHex(wasmModule.__getString(wasmModule.TestWithStruct()))).toEqual(libm.data_struct_encode(st.ref()));
        st.data = 100;
        st.other = 15;
        expect(wasmU8aStringToHex(wasmModule.__getString(wasmModule.TestWithStruct()))).toEqual(libm.data_struct_encode(st.ref()));
    });
    // Enum
    it('encode enum', (): void => {
        const st = new EnumStruct;
        st.a = 1;
        expect(wasmU8aStringToHex(wasmModule.__getString(wasmModule.TestWithEnum()))).toEqual(libm.data_enum_encode(st.ref()));
        const st2 = new EnumStruct;
        st2.b = 32;
        expect(wasmU8aStringToHex(wasmModule.__getString(wasmModule.TestWithEnum()))).toEqual(libm.data_enum_encode(st2.ref()));

    });
    // tuple
    it('encode tuple(u32,u32)', (): void => {
        const st = new TupleType;
        st.a = 10;
        st.b = 1;
        expect(wasmU8aStringToHex(wasmModule.__getString(wasmModule.TestWithTuple(10, 1)))).toEqual(libm.tuple_u32u32_encode(st.ref()));
        st.b = 86400
        st.a = 86400
        expect(wasmU8aStringToHex(wasmModule.__getString(wasmModule.TestWithTuple(86400, 86400)))).toEqual(libm.tuple_u32u32_encode(st.ref()));
    });
    //
    //
    it('[u32;6]', (): void => {
        const IntArray = ArrayType('uint32');
        // @ts-ignore
        let array = new IntArray([1, 2, 3, 4, 5, 6]);
        expect(wasmU8aStringToHex(wasmModule.__getString(wasmModule.TestWithFixedArray()))).toEqual(libm.fixU32_encode(array.buffer, 6));
        array = new IntArray([0, 0, 0, 0, 0, 0]);
        expect(wasmU8aStringToHex(wasmModule.__getString(wasmModule.TestWithFixedArray()))).toEqual(libm.fixU32_encode(array.buffer, 6));
    });

    it('vec<u32>', (): void => {
        const IntArray = ArrayType('uint32');
        let array = new IntArray([1, 2, 3, 4, 5, 6]);
        expect(wasmU8aStringToHex(wasmModule.__getString(wasmModule.TestWithVecU32()))).toEqual(libm.vec_u32_encode(array.buffer, 6));
        array = new IntArray([0, 0, 0, 0, 0, 0]);
        expect(wasmU8aStringToHex(wasmModule.__getString(wasmModule.TestWithVecU32()))).toEqual(libm.vec_u32_encode(array.buffer, 6));
    });

})