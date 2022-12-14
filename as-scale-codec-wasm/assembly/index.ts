import {Bool, BytesReader, CompactInt, IntArray, ScaleMap, ScaleString, UInt32} from "as-scale-codec"

export function TestWithCompactU32(value: u32): string {
    const compactU32 = new CompactInt(value)
    if (CompactInt.fromU8a(compactU32.toU8a()) == compactU32) {
        return compactU32.toU8a().toString()
    }
    return ""
}


export function TestWithBool(value: boolean): string {
    const boolValue = new Bool(value)
    if (Bool.fromU8a(boolValue.toU8a()) == boolValue) {
        return boolValue.toU8a().toString()
    }
    return ""
}

export function TestWithString(value: string): string {
    const stringValue = new ScaleString(value)
    if (ScaleString.fromU8a(stringValue.toU8a()) == stringValue) {
        return stringValue.toHexString()
    }
    return ""
}

export function TestWithTuple(value1: u32, value2: u32): string {
    const scaleMap = new ScaleMap<UInt32, UInt32>();
    scaleMap.set(new UInt32(value1), new UInt32(value2));

    const ScaleMapValue = BytesReader.decodeInto<ScaleMap<UInt32, UInt32>>(scaleMap.toU8a())
    if (ScaleMapValue == scaleMap) {
        return scaleMap.toU8a().toString()
    }
    // ScaleMap<Int32, Bool>.fromU8a([4, 1, 0, 0, 0, 0]);
    return ""
}

// not support
export function TestWithOptionBool(): string {
    return ""
}

// not support
export function TestWithResultU32String(): string {
    return ""
}

// not support
export function TestWithStruct(): string {
    return ""
}

// not support
export function TestWithEnum(): string {
    return ""
}

// not support
export function TestWithFixedArray(): string {
    return ""
}

// not support
export function TestWithVecU32(): string {
    return ""
}
