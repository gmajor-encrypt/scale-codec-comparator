let ffi = require('ffi-napi');
let path = require('path');
const ref = require('ref-napi')
const Struct = require('ref-struct-di')(ref);

let rootPath = path.resolve(path.dirname(path.dirname(__dirname)))

// const box = Struct({
//     width: ref.types.int,
//     height: ref.types.int
// });

const resultsType = Struct({
    ok: ref.types.uint,
    err: 'string'
});

const CodecStruct = Struct({
    data: "uint",
    other: 'uint8'
});


let libm = ffi.Library(rootPath + "/lib/libscale_ffi.dylib", {
    'compact_u32_encode': ['string', ['uint']],
    'compact_u32_decode': ['uint', ['string']],
    'option_bool_encode': ['string', ['string']],
    'option_bool_decode': ['string', ['string']],
    'bool_encode': ['string', [ffi.types.bool]],
    'bool_decode': [ffi.types.bool, ['string']],
    'results_encode': ['string', ['uint', 'string', 'string']],
    'results_decode': [ref.refType(resultsType), ['string']],
    'data_struct_encode': ['string', [ref.refType(CodecStruct)]],
    'data_struct_decode': [ref.refType(CodecStruct), ['string']],

});

describe('Data', (): void => {
    it('encodes u32', (): void => {
        expect(
            "08"
        ).toEqual(libm.compact_u32_encode(2));
    });
    it('decode u32', (): void => {
        expect(
            2
        ).toEqual(libm.compact_u32_decode("08"));
    });
    it('encodes option<bool>', (): void => {
        expect(
            "00"
        ).toEqual(libm.option_bool_encode("NONE"));
    });
    it('decode option<bool>', (): void => {
        expect(
            "true"
        ).toEqual(libm.option_bool_decode("01"));
    });
    it('encodes bool', (): void => {
        expect(
            "01"
        ).toEqual(libm.bool_encode(true));
    });
    it('decode bool', (): void => {
        expect(
            true
        ).toEqual(libm.bool_decode("01"));
    });
    it('encode result<u32,string>', (): void => {
        expect(
            "0002000000"
        ).toEqual(libm.results_encode(2, "NONE", "OK"));
    });

    it('decode result<u32,string>', (): void => {
        let result = libm.results_decode("0002000000")
        expect(
            {"err": "", "ok": 2}
        ).toEqual(result.deref().toJSON());
    });
    it('encode struct', (): void => {
        const st = new CodecStruct;
        st.data = 10;
        st.other = 1;
        expect(
            "0a00000001"
        ).toEqual( libm.data_struct_encode(st.ref()));
    });
    it('decode struct', (): void => {
        let value =  libm.data_struct_decode("0a00000001")
        expect(
            {"data": 10, "other": 1}
        ).toEqual( value.deref().toJSON());
    });
})