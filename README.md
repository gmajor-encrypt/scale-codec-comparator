# Scale-codec-comparator

[![scale-go-test](https://github.com/gmajor-encrypt/scale-codec-comparator/actions/workflows/scale-go.yml/badge.svg)](https://github.com/gmajor-encrypt/scale-codec-comparator/actions/workflows/scale-go.yml)
[![scale-php-test](https://github.com/gmajor-encrypt/scale-codec-comparator/actions/workflows/scale-php.yml/badge.svg)](https://github.com/gmajor-encrypt/scale-codec-comparator/actions/workflows/scale-php.yml)
[![scale-ruby-test](https://github.com/gmajor-encrypt/scale-codec-comparator/actions/workflows/scale-ruby.yml/badge.svg)](https://github.com/gmajor-encrypt/scale-codec-comparator/actions/workflows/scale-ruby.yml)
[![scale-python-test](https://github.com/gmajor-encrypt/scale-codec-comparator/actions/workflows/scale-python.yml/badge.svg)](https://github.com/gmajor-encrypt/scale-codec-comparator/actions/workflows/scale-python.yml)
[![scale-js-test](https://github.com/gmajor-encrypt/scale-codec-comparator/actions/workflows/scale-js.yml/badge.svg)](https://github.com/gmajor-encrypt/scale-codec-comparator/actions/workflows/scale-js.yml)
[![go-substrate-rpc-client-test](https://github.com/gmajor-encrypt/scale-codec-comparator/actions/workflows/go-substrate-rpc-client.yml/badge.svg)](https://github.com/gmajor-encrypt/scale-codec-comparator/actions/workflows/go-substrate-rpc-client.yml)



To date, there are [10 published](https://docs.substrate.io/reference/scale-codec/) implementations of the SCALE Codec. Since each is implemented by a different team & [the reference implementation](https://github.com/paritytech/parity-scale-codec) still introduces small fixes,
it would be beneficial to compile a table of feature-completeness. This would provide (some) assurance that the implementation in a given language is safe & sound to use.

Scale-codec-comarator provides the FFI function of the parity-scale-codec library, which can compare the implementation results of various SCALE Codecs

[GitHub action](https://github.com/gmajor-encrypt/scale-codec-comparator/tree/main/.github/workflows) is integrated to run unit tests using the FFI of Rust lib

The tests of the following scale libraries have been completed

- [x] scale.go https://github.com/itering/scale.go
- [x] scale.rb https://github.com/itering/scale.rb
- [x] php-scale-codec https://github.com/gmajor-encrypt/php-scale-codec
- [x] polkadot-js https://github.com/polkadot-js/api
- [x] py-scale-codec https://github.com/polkascan/py-scale-codec
- [x] cScale https://github.com/MatthewDarnell/cScale
- [x] as-scale-codec https://github.com/LimeChain/as-scale-codec
- [x] go-substrate-rpc-client https://github.com/centrifuge/go-substrate-rpc-client/tree/master/scale
- [x] scale-codec-js-library https://github.com/soramitsu/scale-codec-js-library
- [x] scale-codec-cpp https://github.com/soramitsu/scale-codec-cpp
- [x] scale-ts https://github.com/unstoppablejs/unstoppablejs/tree/main/packages/scale-ts#scale-ts


## Build

```bash
make
```

## How to test

The FFI are definitions here https://github.com/gmajor-encrypt/scale-codec-comparator/blob/main/src/scale_ffi.h


## How to Compare

We use [the reference implementation](https://github.com/paritytech/parity-scale-codec) as the standard result to
compare with the results of other implementations of the SCALE Codec test.
If the test fails, the implementation is inconsistent with parity-scale.
You can simply get the test results from the top badges.

### scale.go
```bash
cd scale.go && go test -v ./...
```

### php-scale-codec
```bash
cd php-scale-codec && composer install && make
```

### scale.rb
```bash
cd scale.rb && bundle install && bundle exec rspec spec/base_spec.rb
```

### polkadot-js

```bash
cd polkadot-js && npm install && npm run test
```

### py-scale-codec

```bash
 cd py-scale-codec && pip install -r requirements.txt && python -m unittest discover
```

### scale-ts

```bash
cd scale-ts && npm install && npm run test
```

### scale-codec-js-library

```bash
cd scale-codec-js-library && npm install && npm run test
```

### go-substrate-rpc-client
```bash
cd go-substrate-rpc-client && go test -v ./...
```

### as-scale-codec
```bash
cd as-scale-codec-wasm && npm run asbuild  ## build as-scale-codec wasm
cd ../as-scale-codec && npm run test

```

### cScale
```bash
cd cScale && git submodule update --init --recursive && cmake --build . && ./MyProject
```
 

### scale-codec-cpp
```bash
cd scale-codec-cpp && cmake . && cmake --build . --target scaleCodecCpp -j 8 && ./scaleCodecCpp
```


## License

The package is available as open source under the terms of the [MIT License](https://opensource.org/licenses/MIT)
