import {
    TestWithBool,
    TestWithCompactU32,
    TestWithEnum,
    TestWithFixedArray,
    TestWithOptionBool,
    TestWithResultU32String,
    TestWithString,
    TestWithStruct,
    TestWithTuple,
    TestWithVecU32
} from "../assembly"


describe("Base function", () => {
    // compact<u32>
    expect(TestWithCompactU32(2)).toStrictEqual("8");
    expect(TestWithCompactU32(20)).toStrictEqual("80");
    expect(TestWithCompactU32(100)).toStrictEqual("145,1");

    // bool
    expect(TestWithBool(true)).toStrictEqual("1");
    expect(TestWithBool(false)).toStrictEqual("0");

    // String
    expect(TestWithString("HELLO WORLD")).toStrictEqual("0x48454c4c4f20574f524c44");
    expect(TestWithString("Hamlet")).toStrictEqual("0x48616d6c6574");

    // Array
    expect(TestWithVecU32()).toStrictEqual("");

    // tuple scale map
    expect(TestWithTuple(10, 1)).toStrictEqual("");
    // not support type
    // Option<bool>
    expect(TestWithOptionBool()).toStrictEqual("");
    // result<u32,string>
    expect(TestWithResultU32String()).toStrictEqual("");
    // struct
    expect(TestWithStruct()).toStrictEqual("");
    // enum
    expect(TestWithEnum()).toStrictEqual("");
    // fixed
    expect(TestWithFixedArray()).toStrictEqual("");
});
