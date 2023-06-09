var ABIToken = [
  {
    constant: true,
    inputs: [],
    name: "name",
    outputs: [{ name: "", type: "string" }],
    payable: false,
    stateMutability: "view",
    type: "function",
  },
  {
    constant: false,
    inputs: [
      { name: "_spender", type: "address" },
      { name: "_value", type: "uint256" },
    ],
    name: "approve",
    outputs: [{ name: "", type: "bool" }],
    payable: false,
    stateMutability: "nonpayable",
    type: "function",
  },
  {
    constant: true,
    inputs: [],
    name: "totalSupply",
    outputs: [{ name: "", type: "uint256" }],
    payable: false,
    stateMutability: "view",
    type: "function",
  },
  {
    constant: false,
    inputs: [
      { name: "_from", type: "address" },
      { name: "_to", type: "address" },
      { name: "_value", type: "uint256" },
    ],
    name: "transferFrom",
    outputs: [{ name: "", type: "bool" }],
    payable: false,
    stateMutability: "nonpayable",
    type: "function",
  },
  {
    constant: true,
    inputs: [],
    name: "decimals",
    outputs: [{ name: "", type: "uint8" }],
    payable: false,
    stateMutability: "view",
    type: "function",
  },
  {
    constant: false,
    inputs: [
      { name: "_to", type: "address" },
      { name: "_value", type: "uint256" },
      { name: "_data", type: "bytes" },
    ],
    name: "transferAndCall",
    outputs: [{ name: "success", type: "bool" }],
    payable: false,
    stateMutability: "nonpayable",
    type: "function",
  },
  {
    constant: false,
    inputs: [
      { name: "_spender", type: "address" },
      { name: "_subtractedValue", type: "uint256" },
    ],
    name: "decreaseApproval",
    outputs: [{ name: "success", type: "bool" }],
    payable: false,
    stateMutability: "nonpayable",
    type: "function",
  },
  {
    constant: true,
    inputs: [{ name: "_owner", type: "address" }],
    name: "balanceOf",
    outputs: [{ name: "balance", type: "uint256" }],
    payable: false,
    stateMutability: "view",
    type: "function",
  },
  {
    constant: true,
    inputs: [],
    name: "symbol",
    outputs: [{ name: "", type: "string" }],
    payable: false,
    stateMutability: "view",
    type: "function",
  },
  {
    constant: false,
    inputs: [
      { name: "_to", type: "address" },
      { name: "_value", type: "uint256" },
    ],
    name: "transfer",
    outputs: [{ name: "success", type: "bool" }],
    payable: false,
    stateMutability: "nonpayable",
    type: "function",
  },
  {
    constant: false,
    inputs: [
      { name: "_spender", type: "address" },
      { name: "_addedValue", type: "uint256" },
    ],
    name: "increaseApproval",
    outputs: [{ name: "success", type: "bool" }],
    payable: false,
    stateMutability: "nonpayable",
    type: "function",
  },
  {
    constant: true,
    inputs: [
      { name: "_owner", type: "address" },
      { name: "_spender", type: "address" },
    ],
    name: "allowance",
    outputs: [{ name: "remaining", type: "uint256" }],
    payable: false,
    stateMutability: "view",
    type: "function",
  },
  {
    inputs: [],
    payable: false,
    stateMutability: "nonpayable",
    type: "constructor",
  },
  {
    anonymous: false,
    inputs: [
      { indexed: true, name: "from", type: "address" },
      { indexed: true, name: "to", type: "address" },
      { indexed: false, name: "value", type: "uint256" },
      { indexed: false, name: "data", type: "bytes" },
    ],
    name: "Transfer",
    type: "event",
  },
  {
    anonymous: false,
    inputs: [
      { indexed: true, name: "owner", type: "address" },
      { indexed: true, name: "spender", type: "address" },
      { indexed: false, name: "value", type: "uint256" },
    ],
    name: "Approval",
    type: "event",
  },
];
// 'use strict';
const bodyparser = require("body-parser");
var port = process.env.PORT || 6545;
var Web3 = require("web3");
var Common = require("ethereumjs-common").default;
const Transaction = require("ethereumjs-tx").Transaction;
var url = require("url");
var express = require("express");
var app = express();
var cors = require("cors");
app.use(cors());
app.use(express.json());
app.use(bodyparser.urlencoded({ extended: true }));
  
const decimal = 18;
// var provider = 'https://gaurascan.com/api/eth-rpc';
// var provider = 'https://testnet-rpc.gaurascan.com';

var provider = new Web3.providers.HttpProvider(
  "https://mainnet-rpc.nordekscan.com"
);
//var provider = new Web3.providers.HttpProvider('https://data-seed-prebsc-1-s1.binance.org:8545/');
const web3 = new Web3(provider);

app.get("/", function (req, res) {
  // const gasWei = web3.eth.getGasPrice();
  // const gasGwei = web3.utils.fromWei(gasWei, 'gwei');
  var data = "CHIANLINK service are up and running...: ";
  // web3.eth.getGasPrice(). then(console.log);
  res.send(data);
});

app.get("/depositAddress", function (req, res) {
  var data = web3.eth.accounts.create();
  data.status = "true";
  res.send(data);
});

app.get("/getBalance", function (req, res) {
  var data = {};
  var queryData = url.parse(req.url, true).query;
  try {
    web3.eth
      .getBalance(queryData.address)
      .then((_balance) => {
        data.address = queryData.address;
        data.balance = _balance;
        data.status = true;
        res.send(data);
      })
      .catch((_err) => {
        data.message = "Provided address " + queryData.address + " is invalid.";
        data.status = false;
        res.send(data);
      });
  } catch (ex) {
    data.message = "Provided address " + queryData.address + " is invalid.";
    data.status = false;
    res.send(data);
  }
});

app.get("/getBalanceOf", function (req, res) {
  var data = { res: "empty" };
  var queryData = url.parse(req.url, true).query;
  //   res.send(queryData);
  //   return;
  try {
    const myContract = new web3.eth.Contract(
      ABIToken,
      queryData.tokenContractAddress
    );
    myContract.methods
      .balanceOf(queryData.address)
      .call()
      .then((_balance) => {
        data.address = queryData.address;
        data.token = queryData.tokenContractAddress;
        data.quantity = _balance;
        data.status = true;
        res.send(data);
      })
      .catch((_err) => {
        data.message =
          "Provided address " +
          queryData.address +
          " is invalid for token " +
          queryData.tokenContractAddress;
        data.status = false;
        res.send(data);
      });
  } catch (ex) {
    data.message =
      "Provided address " +
      queryData.address +
      " is invalid for token " +
      queryData.tokenContractAddress;
    data.status = false;
    res.send(data);
  }
});

app.post("/userSendToken", function (req, res) {
  var data = {};
  var param = req.body;
  try {
    const myContract = new web3.eth.Contract(
      ABIToken,
      param.tokenContractAddress
    );
    var account = web3.eth.accounts.privateKeyToAccount(param.PrivateKey);
    web3.eth.accounts
      .signTransaction(
        {
          to: param.tokenContractAddress,
          value: 0,
          // gas: 84000,
          gas: 0,
          data: myContract.methods
            .transfer(param.ToAddress, param.Amount)
            .encodeABI(),
        },
        account.privateKey
      )
      .then((signedTransaction) => {
        web3.eth
          .sendSignedTransaction(signedTransaction.rawTransaction)
          .on("error", function (error) {
            data.message = error.message;
            data.status = false;
            res.send(data);
          })
          .on("transactionHash", function (hash) {
            data.message = "Transaction Initiated";
            data.hash = hash;
            data.status = true;
            res.send(data);
          })
          .on("receipt", function (receipt) {});
      })
      .catch(function (fallback) {
        data.message = fallback.message;
        data.status = false;
        res.send(data);
      });
  } catch (err) {
    data.message = err.message;
    data.status = false;
    res.send(data);
  }
});

app.post("/sendTransaction", function (req, res) {
  var data = {};
  var param = req.body;
  // console.log('params: '+param.ToAddress)
  try {
    var account = web3.eth.accounts.privateKeyToAccount(param.PrivateKey);
    web3.eth.accounts
      .signTransaction(
        {
          to: param.ToAddress,
          value: param.Amount,
          // gas: 42000
          gas: 42000,
        },
        account.privateKey
      )
      .then((signedTransaction) => {
        web3.eth
          .sendSignedTransaction(signedTransaction.rawTransaction)
          .on("error", function (error) {
            data.message = error.message;
            data.status = true;
            res.send(data);
          })
          .on("transactionHash", function (hash) {
            data.message = "Transaction Initiated";
            data.hash = hash;
            data.status = true;
            res.send(data);
          })
          .on("receipt", function (receipt) {
            //res.send(receipt);
          });
      })
      .catch(function (fallback) {
        data.message = fallback.message;
        data.status = false;
        res.send(data);
      });
  } catch (err) {
    data.message = err.message;
    data.status = false;
    res.send(data);
  }
});

app.listen(port, () => {
  console.log(`app listening at ${port}`);
});