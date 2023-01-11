cmake_minimum_required(VERSION 3.22)

include(FetchContent)

FetchContent_Declare(scale URL https://github.com/soramitsu/scale-codec-cpp/archive/refs/tags/v1.0.6.tar.gz)

FetchContent_GetProperties(scale)
if (NOT scale_POPULATED)
    FetchContent_Populate(scale)
    add_subdirectory(${scale_SOURCE_DIR} ${scale_BINARY_DIR} EXCLUDE_FROM_ALL)
endif ()

project(scaleCodecCpp)

set(CMAKE_CXX_STANDARD 17)

add_executable(${PROJECT_NAME} main.cpp)

target_link_libraries(${PROJECT_NAME} scale)