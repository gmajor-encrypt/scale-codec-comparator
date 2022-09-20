# Scale-codec-comparator

[![scale-go-test](https://github.com/gmajor-encrypt/scale-codec-comparator/actions/workflows/scale-go.yml/badge.svg)](https://github.com/gmajor-encrypt/scale-codec-comparator/actions/workflows/scale-go.yml)
[![scale-php-test](https://github.com/gmajor-encrypt/scale-codec-comparator/actions/workflows/scale-php.yml/badge.svg)](https://github.com/gmajor-encrypt/scale-codec-comparator/actions/workflows/scale-php.yml)
[![scale-ruby-test](https://github.com/gmajor-encrypt/scale-codec-comparator/actions/workflows/scale-ruby.yml/badge.svg)](https://github.com/gmajor-encrypt/scale-codec-comparator/actions/workflows/scale-ruby.yml)
[![scale-python-test](https://github.com/gmajor-encrypt/scale-codec-comparator/actions/workflows/scale-python.yml/badge.svg)](https://github.com/gmajor-encrypt/scale-codec-comparator/actions/workflows/scale-python.yml)
[![scale-js-test](https://github.com/gmajor-encrypt/scale-codec-comparator/actions/workflows/scale-js.yml/badge.svg)](https://github.com/gmajor-encrypt/scale-codec-comparator/actions/workflows/scale-js.yml)


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
- [ ] cScale https://github.com/MatthewDarnell/cScale
- [ ] as-scale-codec https://github.com/LimeChain/as-scale-codec
- [ ] hs-web3 https://github.com/airalab/hs-web3/
- [ ] polkaj https://github.com/emeraldpay/polkaj
- [ ] scale-codec-cpp https://github.com/soramitsu/scale-codec-cpp
- [ ] scale-ts https://github.com/unstoppablejs/unstoppablejs/tree/main/packages/scale-ts#scale-ts


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



## License

The package is available as open source under the terms of the [MIT License](https://opensource.org/licenses/MIT)
