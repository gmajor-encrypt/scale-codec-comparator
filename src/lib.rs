extern crate parity_scale_codec;

use std::ffi::{CStr, CString};
use std::fmt::Error;
use std::slice;
use std::time::Duration;

use libc::c_char;
use parity_scale_codec::{Compact, Decode, Encode, OptionBool, Output, WrapperTypeEncode};

#[no_mangle]
pub extern "C" fn rustdemo(name: *const libc::c_char) -> *const libc::c_char {
    let cstr_name = unsafe { CStr::from_ptr(name) };
    let mut str_name = cstr_name.to_str().unwrap().to_string();
    println!("Rust get Input:  \"{}\"", str_name);
    let r_string: &str = " Rust say: Hello Go ";
    str_name.push_str(r_string);
    CString::new(str_name).unwrap().into_raw()
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

    let compact_u32a = Compact(0u32);
    assert_eq!(hexify(&compact_u32a.encode()), "00");
    let compact_u32b = Compact(4294967295u32);
    assert_eq!(hexify(&compact_u32b.encode()), "03 ff ff ff ff");

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
    let fixed_u8 = [0u8;6];
    assert_eq!(hexify(&fixed_u8.encode()), "00 00 00 00 00 00");

    let fixed_u32 = [1u32;6];
    assert_eq!(hexify(&fixed_u32.encode()), "01 00 00 00 01 00 00 00 01 00 00 00 01 00 00 00 01 00 00 00 01 00 00 00");
}