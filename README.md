# scale-codec-comparator

[![scale-go-test](https://github.com/gmajor-encrypt/scale-codec-comparator/actions/workflows/scale-go.yml/badge.svg)](https://github.com/gmajor-encrypt/scale-codec-comparator/actions/workflows/scale-go.yml)
[![scale-php-test](https://github.com/gmajor-encrypt/scale-codec-comparator/actions/workflows/scale-php.yml/badge.svg)](https://github.com/gmajor-encrypt/scale-codec-comparator/actions/workflows/scale-php.yml)
[![scale-ruby-test](https://github.com/gmajor-encrypt/scale-codec-comparator/actions/workflows/scale-ruby.yml/badge.svg)](https://github.com/gmajor-encrypt/scale-codec-comparator/actions/workflows/scale-ruby.yml)
[![scale-python-test](https://github.com/gmajor-encrypt/scale-codec-comparator/actions/workflows/scale-python.yml/badge.svg)](https://github.com/gmajor-encrypt/scale-codec-comparator/actions/workflows/scale-python.yml)
[![scale-js-test](https://github.com/gmajor-encrypt/scale-codec-comparator/actions/workflows/scale-js.yml/badge.svg)](https://github.com/gmajor-encrypt/scale-codec-comparator/actions/workflows/scale-js.yml)


scale-codec-comparator provides the FFI function of some functions of the parity-scale-codec library, and can compare the output results as needed

The tests of the following scale libraries have been completed

- [x] scale.go
- [x] scale.rb
- [x] php-scale-codec
- [x] polkadot-js
- [x] py-scale-codec
- [ ] cScale
- [ ] as-scale-codec
- [ ] hs-web3
- [ ] polkaj
- [ ] scale-codec-cpp
- [ ] scale-ts


## Build

```bash
make
```

## How to test

### scale.go
```bash
cd scale.go && go test -v ./...
```

#### php-scale-codec
```bash
cd php-scale-codec && composer install && make
```

#### scale.rb
```bash
cd scale.rb && bundle install && bundle exec rspec spec/base_spec.rb
```

#### polkadot-js

```bash
cd polkadot-js && npm install && npm run test
```

#### py-scale-codec

```bash
 cd py-scale-codec && pip install -r requirements.txt && python -m unittest discover
```



## License

The package is available as open source under the terms of the [MIT License](https://opensource.org/licenses/MIT)
