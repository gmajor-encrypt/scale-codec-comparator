extern crate parity_scale_codec;

use std::ffi::{CStr, CString};
use std::time::Duration;

use parity_scale_codec::{Compact, Decode, Encode, OptionBool};

// compact u32 encode
#[no_mangle]
pub extern "C" fn compact_u32_encode(u32: u32) -> *const libc::c_char {
    let compact_u32a = Compact(u32);
    CString::new(hex::encode(compact_u32a.encode())).unwrap().into_raw()
}

// compact u32 decode
#[no_mangle]
pub extern "C" fn compact_u32_decode(u32_encode: *const libc::c_char) -> u32 {
    let cstr_u32 = unsafe { CStr::from_ptr(u32_encode) };
    let str_u32 = cstr_u32.to_str().unwrap().to_string();
    let bytes_u32 = hex::decode(str_u32).unwrap();
    <Compact<u32>>::decode(&mut &bytes_u32[..]).unwrap().0
}


// option<bool> encode
#[no_mangle]
pub extern "C" fn option_bool_encode(raw: *const libc::c_char) -> *const libc::c_char {
    let cstr_bool = unsafe { CStr::from_ptr(raw) };
    let str_bool = cstr_bool.to_str().unwrap();
    let option_bool = match str_bool {
        "none" | "null" | "NULL" | "None" => OptionBool(None),
        "true" => OptionBool(Some(true)),
        "false" => OptionBool(Some(false)),
        _ => OptionBool(None)
    };
    CString::new(hex::encode(option_bool.encode())).unwrap().into_raw()
}

// option<bool> decode
// #[no_mangle]
// it will be return "None","true","false"
#[no_mangle]
pub extern "C" fn option_bool_decode(raw: *const libc::c_char) -> *const libc::c_char {
    let cstr_bool = unsafe { CStr::from_ptr(raw) };
    let str_bool = cstr_bool.to_str().unwrap();
    let bytes_bool = hex::decode(str_bool).unwrap();
    let option_bool = OptionBool::decode(&mut &bytes_bool[..]).unwrap().0;
    let bool_string = match option_bool {
        None => "None",
        Some(true) => "true",
        Some(false) => "false",
    };
    CString::new(bool_string).unwrap().into_raw()
}

// bool decode
#[no_mangle]
pub extern "C" fn bool_decode(bool_encode: *const libc::c_char) -> bool {
    let cstr_bool = unsafe { CStr::from_ptr(bool_encode) };
    let str_bool = cstr_bool.to_str().unwrap().to_string();
    let bytes_bool = hex::decode(str_bool).unwrap();
    bool::decode(&mut &bytes_bool[..]).unwrap()
}

// bool encode
#[no_mangle]
pub extern "C" fn bool_encode(raw: bool) -> *const libc::c_char {
    CString::new(hex::encode(raw.encode())).unwrap().into_raw()
}

#[repr(C)]
#[derive(Debug, PartialEq)]
pub struct ResultsType {
    ok: u32,
    err: *const libc::c_char,
}

// results define result<u32,Err>
// result can be Err or Ok, err_message is err message
#[no_mangle]
pub extern "C" fn results_encode(ptr: *mut ResultsType) -> *const libc::c_char {
    assert!(!ptr.is_null());
    let value = unsafe { &mut *ptr };
    let ok: Result<u32, &str> = Ok(value.ok);
    let cstr_err = unsafe { CStr::from_ptr(value.err) };
    let str_err = cstr_err.to_str().unwrap();
    let err: Result<u32, &str> = Err(str_err);
    let result_string = match str_err.is_empty() {
        true => CString::new(hex::encode(ok.encode())).unwrap().into_raw(),
        false => CString::new(hex::encode(err.encode())).unwrap().into_raw(),
    };
    result_string
}

#[no_mangle]
pub extern "C" fn results_decode(raw: *const libc::c_char) -> *mut ResultsType {
    let str_raw = unsafe { CStr::from_ptr(raw) }.to_str().unwrap().to_string();
    let bytes_raw = hex::decode(str_raw).unwrap();
    // Result<u32, &str>
    let result_value = <Result<u32, String>>::decode(&mut &bytes_raw[..]).unwrap();
    match result_value {
        Ok(u) => Box::into_raw(Box::new(ResultsType { ok: u, err: CString::new("").unwrap().into_raw() })),
        Err(e) => Box::into_raw(Box::new(ResultsType { ok: 0, err: CString::new(e).unwrap().into_raw() })),
    }
}


#[repr(C)]
#[derive(Debug, PartialEq, Encode, Decode)]
pub struct CodecStruct {
    data: u32,
    other: u8,
}

// CodecStruct encode
#[no_mangle]
pub extern "C" fn data_struct_encode(ptr: *mut CodecStruct) -> *const libc::c_char {
    assert!(!ptr.is_null());
    let value = unsafe { &mut *ptr };
    CString::new(hex::encode(value.encode())).unwrap().into_raw()
}

// CodecStruct encode
#[no_mangle]
pub extern "C" fn data_struct_decode(raw: *const libc::c_char) -> *mut CodecStruct {
    let str_raw = unsafe { CStr::from_ptr(raw) }.to_str().unwrap().to_string();
    let bytes_raw = hex::decode(str_raw).unwrap();
    Box::into_raw(Box::new(CodecStruct::decode(&mut &bytes_raw[..]).unwrap()))
}

#[repr(C)]
#[derive(Debug, PartialEq, Encode, Decode)]
pub enum EnumType {
    A(u32),
    B(u32),
    C(u32),
}

#[repr(C)]
pub struct EnumStruct {
    a: u32,
    b: u32,
    c: u32,
}

#[no_mangle]
pub extern "C" fn data_enum_encode(ptr: *mut EnumStruct) -> *const libc::c_char {
    // CString::new(hex::encode(value.encode())).unwrap().into_raw()
    assert!(!ptr.is_null());
    let value = unsafe { &mut *ptr };
    if value.a > 0 {
        CString::new(hex::encode(EnumType::A(value.a).encode())).unwrap().into_raw()
    } else if value.b > 0 {
        CString::new(hex::encode(EnumType::B(value.b).encode())).unwrap().into_raw()
    } else {
        CString::new(hex::encode(EnumType::C(value.c).encode())).unwrap().into_raw()
    }
}

// CodecStruct encode
#[no_mangle]
pub extern "C" fn data_enum_decode(raw: *const libc::c_char) -> *mut EnumStruct {
    let str_raw = unsafe { CStr::from_ptr(raw) }.to_str().unwrap().to_string();
    let bytes_raw = hex::decode(str_raw).unwrap();
    let option_enum = EnumType::decode(&mut &bytes_raw[..]).unwrap();
    match option_enum {
        EnumType::A(u32) => Box::into_raw(Box::new(EnumStruct { a: u32, b: 0, c: 0 })),
        EnumType::B(u32) => Box::into_raw(Box::new(EnumStruct { a: 0, b: u32, c: 0 })),
        EnumType::C(u32) => Box::into_raw(Box::new(EnumStruct { a: 0, b: 0, c: u32 })),
    }
}


// String encode
// bool decode
#[no_mangle]
pub extern "C" fn string_decode(raw: *const libc::c_char) -> *const libc::c_char {
    let str_raw = unsafe { CStr::from_ptr(raw) }.to_str().unwrap().to_string();
    let bytes_raw = hex::decode(str_raw).unwrap();
    CString::new(String::decode(&mut &bytes_raw[..]).unwrap()).unwrap().into_raw()
}

// string encode
#[no_mangle]
pub extern "C" fn string_encode(raw: *const libc::c_char) -> *const libc::c_char {
    let str_raw = unsafe { CStr::from_ptr(raw) }.to_str().unwrap().to_string();
    CString::new(hex::encode(str_raw.encode())).unwrap().into_raw()
}

#[no_mangle]
pub extern "C" fn fixU32_decode(raw: *const libc::c_char) -> *mut u32 {
    let str_raw = unsafe { CStr::from_ptr(raw) }.to_str().unwrap().to_string();
    let bytes_raw = hex::decode(str_raw).unwrap();
    let mut u32_fixed: [u32; 6] = <[u32; 6]>::decode(&mut &bytes_raw[..]).unwrap();
    let mut u32_vec = u32_fixed.to_vec();
    let ptr = u32_vec.as_mut_ptr();
    std::mem::forget(u32_vec);
    ptr
}

#[no_mangle]
pub extern "C" fn fixU32_encode(ptr: *const u32, length: libc::size_t) -> *const libc::c_char {
    assert!(!ptr.is_null());
    let slice = unsafe {
        std::slice::from_raw_parts(ptr, length as usize)
    };
    let mut arr = [0u32; 6];
    for (&x, p) in slice.iter().zip(arr.iter_mut()) {
        *p = x;
    }
    CString::new(hex::encode(arr.encode())).unwrap().into_raw()
}


#[no_mangle]
pub extern "C" fn vec_u32_encode(ptr: *const u32, length: usize) -> *const libc::c_char {
    assert!(!ptr.is_null());
    let slice = unsafe {
        std::slice::from_raw_parts(ptr, length)
    };
    CString::new(hex::encode(slice.encode())).unwrap().into_raw()
}

#[no_mangle]
pub extern "C" fn vec_u32_decode(raw: *const libc::c_char) -> *mut u32 {
    let str_raw = unsafe { CStr::from_ptr(raw) }.to_str().unwrap().to_string();
    let bytes_raw = hex::decode(str_raw).unwrap();
    let mut vec32 = <Vec<u32>>::decode(&mut &bytes_raw[..]).unwrap();
    let ptr = vec32.as_mut_ptr();
    std::mem::forget(vec32);
    ptr
}


// tuple
#[repr(C)]
#[derive(Debug, PartialEq, Encode, Decode)]
pub struct TupleType {
    a: u32,
    b: u32,
}


#[no_mangle]
pub extern "C" fn tuple_u32u32_encode(ptr: *mut TupleType) -> *const libc::c_char {
    assert!(!ptr.is_null());
    let t = unsafe { &mut *ptr };
    CString::new(hex::encode(t.encode())).unwrap().into_raw()
}

#[no_mangle]
pub extern "C" fn tuple_u32u32_decode(raw: *const libc::c_char) -> *mut TupleType {
    let str_raw = unsafe { CStr::from_ptr(raw) }.to_str().unwrap().to_string();
    let bytes_raw = hex::decode(str_raw).unwrap();
    Box::into_raw(Box::new(TupleType::decode(&mut &bytes_raw[..]).unwrap()))
}


// https://github.com/paritytech/parity-scale-codec
// https://docs.substrate.io/v3/advanced/scale-codec/
// Fixed-width integers     https://docs.substrate.io/v3/advanced/scale-codec/#fixed-width-integers
// input t=[u8; 4] [1,2,3,4] & FixedLength 3

// Compact/general integers https://docs.substrate.io/v3/advanced/scale-codec/#compactgeneral-integers
// input num 0|1|65535|2**32-1|2**64-1|2**128-1

// Boolean                  https://docs.substrate.io/v3/advanced/scale-codec/#boolean
// input bool true|false

// Option                   https://docs.substrate.io/v3/advanced/scale-codec/#options
// input t = option<bool> None|false|true

// Results                  https://docs.substrate.io/v3/advanced/scale-codec/#results
// input t = Result<u8, bool> Ok(42)|Err(false)

// Vector                   https://docs.substrate.io/v3/advanced/scale-codec/#vectors-lists-series-sets
// input t = vec<u32>       [1, 2, 3, 4]

// String                   https://docs.substrate.io/v3/advanced/scale-codec/#strings
// input  "Hamlet", "Война и мир", "三国演义"

// Tuples                   https://docs.substrate.io/v3/advanced/scale-codec/#tuples
// input t = (u8, u16, u32)  [1,400,800000]

// Struct                   https://docs.substrate.io/v3/advanced/scale-codec/#data-structures
// input t = {"a":"Compact","b":"Compact"} {"a":"3","b":0}

// Enum                     https://docs.substrate.io/v3/advanced/scale-codec/#enumerations-tagged-unions
// input t = {"int":"u8", "bool":"bool"} {"bool":true}

// Original
// Encode  input(num,bool,null,ok|err,vec,string,map) output(string)
// Decode  input(string) output(num,bool,null,ok|err,vec,string,map)


#[test]
fn codec()
{
    fn hexify(bytes: &[u8]) -> String {
        bytes.iter().map(|ref b| format!("{:02x}", b)).collect::<Vec<String>>().join(" ")
    }

    // Vec<u8>
    let value = vec![0u8, 1, 1, 2, 3, 5, 8, 13, 21, 34];
    let encoded = value.encode();
    assert_eq!(hexify(&encoded), "28 00 01 01 02 03 05 08 0d 15 22");
    assert_eq!(<Vec<u8>>::decode(&mut &encoded[..]).unwrap(), value);
    // assert_eq!::decode(&mut &encoded[..]).unwrap(), value);

    // vec<i16>
    let value = vec![0i16, 1, -1, 2, -2, 3, -3];
    let encoded = value.encode();
    assert_eq!(hexify(&encoded), "1c 00 00 01 00 ff ff 02 00 fe ff 03 00 fd ff");
    assert_eq!(<Vec<i16>>::decode(&mut &encoded[..]).unwrap(), value);

    // vec<option<int>>
    let value = vec![Some(1i8), Some(-1), None];
    let encoded = value.encode();
    assert_eq!(hexify(&encoded), "0c 01 01 01 ff 00");
    assert_eq!(<Vec<Option<i8>>>::decode(&mut &encoded[..]).unwrap(), value);


    let empty_vec: Vec<u8> = vec![];
    let opt_vec = Some(empty_vec);
    assert_eq!(hexify(&opt_vec.encode()),"01 00");
    // assert_eq!(, "0c");


    // vec<option<bool>
    let value = vec![OptionBool(Some(true)), OptionBool(Some(false)), OptionBool(None)];
    let encoded = value.encode();
    assert_eq!(hexify(&encoded), "0c 01 02 00");
    assert_eq!(<Vec<OptionBool>>::decode(&mut &encoded[..]).unwrap(), value);

    // vec<string>
    let value = vec![
        "Hamlet".to_owned(),
        "Война и мир".to_owned(),
        "三国演义".to_owned(),
        "أَلْف لَيْلَة وَلَيْلَة‎".to_owned(),
    ];
    let encoded = value.encode();
    assert_eq!(hexify(&encoded), "10 18 48 61 6d 6c 65 74 50 d0 92 d0 be d0 b9 d0 bd d0 b0 20 d0 \
			b8 20 d0 bc d0 b8 d1 80 30 e4 b8 89 e5 9b bd e6 bc 94 e4 b9 89 bc d8 a3 d9 8e d9 84 d9 92 \
			d9 81 20 d9 84 d9 8e d9 8a d9 92 d9 84 d9 8e d8 a9 20 d9 88 d9 8e d9 84 d9 8e d9 8a d9 92 \
			d9 84 d9 8e d8 a9 e2 80 8e");
    assert_eq!(<Vec<String>>::decode(&mut &encoded[..]).unwrap(), value);

    // enum
    #[derive(Debug, PartialEq, Encode, Decode)]
    enum EnumType {
        #[codec(index = 15)]
        A,
        B(u32, u64),
        C {
            a: u32,
            b: u64,
        },
    }

    let a = EnumType::A;
    let b = EnumType::B(1, 2);
    let c = EnumType::C { a: 1, b: 2 };

    a.using_encoded(|ref slice| {
        assert_eq!(slice, &b"\x0f");
    });

    b.using_encoded(|ref slice| {
        assert_eq!(slice, &b"\x01\x01\0\0\0\x02\0\0\0\0\0\0\0");
    });

    c.using_encoded(|ref slice| {
        assert_eq!(slice, &b"\x02\x01\0\0\0\x02\0\0\0\0\0\0\0");
    });

    let mut da: &[u8] = b"\x0f";
    assert_eq!(EnumType::decode(&mut da).ok(), Some(a));

    let mut db: &[u8] = b"\x01\x01\0\0\0\x02\0\0\0\0\0\0\0";
    assert_eq!(EnumType::decode(&mut db).ok(), Some(b));

    let mut dc: &[u8] = b"\x02\x01\0\0\0\x02\0\0\0\0\0\0\0";
    assert_eq!(EnumType::decode(&mut dc).ok(), Some(c));

    let mut dz: &[u8] = &[0];
    assert_eq!(EnumType::decode(&mut dz).ok(), None);


    // bool
    assert_eq!(true.encode(), vec![1]);
    assert_eq!(false.encode(), vec![0]);
    assert_eq!(bool::decode(&mut &[1][..]).unwrap(), true);
    assert_eq!(bool::decode(&mut &[0][..]).unwrap(), false);

    // u64
    let num_secs = u64::max_value();
    let num_nanos = 0;
    let duration = Duration::new(num_secs, num_nanos);
    let expected = (num_secs, num_nanos).encode();

    assert_eq!(duration.encode(), expected);
    assert_eq!(Duration::decode(&mut &expected[..]).unwrap(), duration);

    #[derive(Debug, PartialEq, Encode, Decode)]
    struct TestStruct {
        data: Vec<u32>,
        other: u8,
        compact: Compact<u128>,
    }

    let ts = TestStruct {
        data: vec![1, 2, 4, 5, 6],
        other: 45,
        compact: Compact(123234545),
    };
    assert_eq!(hexify(&ts.encode()), "14 01 00 00 00 02 00 00 00 04 00 00 00 05 00 00 00 06 00 00 00 2d c6 a3 61 1d");

    let compact_u32a_raw = Compact(0u32).encode();
    assert_eq!(hexify(&compact_u32a_raw), "00");
    <Compact<u32>>::decode(&mut &compact_u32a_raw[..]).unwrap();
    let compact_u32b = Compact(4294967295u32);
    assert_eq!(hexify(&compact_u32b.encode()), "03 ff ff ff ff");
    assert_eq!(hex::encode(&compact_u32b.encode()), "03ffffffff");

    // tuple
    let x = vec![1u8, 2, 3, 4];
    let y = 128i64;

    let encoded = (&x, &y).encode();
    assert_eq!(hexify(&encoded.encode()), "34 10 01 02 03 04 80 00 00 00 00 00 00 00");
    assert_eq!((x, y), Decode::decode(&mut &encoded[..]).unwrap());

    // results ok
    let result: Result<u32, &str> = Ok(1u32);
    assert_eq!(hexify(&result.encode()), "00 01 00 00 00");
    // results error
    let result_fail: Result<u32, &str> = Err("emergency failure");
    assert_eq!(hexify(&result_fail.encode()), "01 44 65 6d 65 72 67 65 6e 63 79 20 66 61 69 6c 75 72 65");

    // fixed vector
    let fixed_u8 = [0u8; 6];
    assert_eq!(hexify(&fixed_u8.encode()), "00 00 00 00 00 00");

    let fixed_u32 = [1u32; 6];
    assert_eq!(hexify(&fixed_u32.encode()), "01 00 00 00 01 00 00 00 01 00 00 00 01 00 00 00 01 00 00 00 01 00 00 00");
}