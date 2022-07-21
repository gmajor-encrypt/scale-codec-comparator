let ffi = require('ffi-napi');
let path = require('path');

let rootPath = path.resolve(path.dirname(path.dirname(__dirname)))
let libm = ffi.Library(rootPath+"/lib/libscale_ffi.dylib", {
    'compact_u32_encode': [ 'string', [ 'int' ] ]
});

describe('Data', (): void => {
    it('encodes a normal None', (): void => {
        expect(
            "04"
        ).toEqual(libm.compact_u32_encode(1));
    });
})