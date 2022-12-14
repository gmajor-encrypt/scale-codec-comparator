module.exports = {
  include: ["tests/*.spec.ts"],
  add: [],
  flags: {
    "--runtime": ["stub"], // Acceptable values are: full, half, stub (arena), and none
  },
  disclude: [/node_modules/],
  imports (memory, createImports, instantiateSync, binary) {
    let instance; // Imports can reference this
    const myImports = {
      // put your web assembly imports here, and return the module
    };
    instance = instantiateSync(binary, createImports(myImports));
    return instance;
  },
  outputBinary: false,
};
