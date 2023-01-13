# as-scale-codec-wasm

Since AssemblyScript does not have a ffi library that can be used directly, 
we compile the AssemblyScript script into a wasm format that js can call, and compare the results of wasm and rust ffi


## Supported types
The following table shows the status of the types and their arrays:

| Type          |             Support             | 
|---------------|:-------------------------------:|
| `Compact`     |                *                |
| `Option`      |                *                |
| `Bool`        |                *                |
| `Tuple`       | Limited Support, has some error | 
| `Strcut`      |           not support           | 
| `Result`      |           not support           | 
| `Enum`        |           not support           | 
| `Fixed array` |           not support           | 
| `Vec`         |         Limited Support         | 


## Requirement

1. nodejs >= 14
2. npm


## Test

```bash
npm run test
```