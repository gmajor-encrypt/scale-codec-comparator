FROM golang:1.17 as builder

ADD src/scale_ffi.h /src/scale_ffi.h
WORKDIR /app

COPY scale.go .
RUN go mod download -x

RUN go test -v ./...
