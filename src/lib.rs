extern crate parity_scale_codec;

use std::ffi::{CStr, CString};
use std::slice;

use libc::c_char;
use parity_scale_codec::{Decode, Encode};

#[no_mangle]
pub extern "C" fn rustdemo(name: *const libc::c_char) -> *const libc::c_char {
    let cstr_name = unsafe { CStr::from_ptr(name) };
    let mut str_name = cstr_name.to_str().unwrap().to_string();
    println!("Rust get Input:  \"{}\"", str_name);
    let r_string: &str = " Rust say: Hello Go ";
    str_name.push_str(r_string);
    CString::new(str_name).unwrap().into_raw()
}

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
fn opt_bool_is_broken()
{
    // does not conform to boolean specification in https://substrate.dev/docs/en/conceptual/core/codec#options
    let v = vec![1, 1];
    assert_eq!(<Option<bool>>::decode(&mut &v[..]).unwrap(), Some(true));

    //this does
    let v = vec![0];
    assert_eq!(<Option<bool>>::decode(&mut &v[..]).unwrap(), None);

    //this does not
    let v = vec![0, 0, 0, 0];
    assert_eq!(<Option<bool>>::decode(&mut &v[..]).unwrap(), None);

    //this does not
    let v = vec![1];
    assert!(<Option<bool>>::decode(&mut &v[..]).is_err());

    //this does not
    let v = vec![2];
    assert!(<Option<bool>>::decode(&mut &v[..]).is_err());

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
}