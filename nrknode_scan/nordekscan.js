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
var provider = new Web3.providers.HttpProvider(
  "https://mainnet-rpc.nordekscan.com"
);

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

app.get("/getAccount", function (req, res) {
  var data = {};
  var queryData = url.parse(req.url, true).query;
  const account = web3.eth.accounts.privateKeyToAccount(queryData.priv_key);
  res.send(JSON.stringify(account));
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
    var tx = myContract.methods
      .transfer(
        queryData.ToAddress,
        web3.utils.toWei(queryData.Amount, "ether")
      )
      .encodeABI();

    web3.eth.accounts
      .signTransaction(
        {
          to: param.tokenContractAddress,
          value: 0,
          gas: 84000,
          // gas: 0,n
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
  try {
    web3.eth.getGasPrice().then((result)=>{
      var gasPrice = web3.utils.fromWei(result,'ether');
      amount = param.Amount - (gasPrice);
      amount = web3.utils.toWei(String(amount), "ether");
     
      var account = web3.eth.accounts.privateKeyToAccount(param.PrivateKey);

    web3.eth.accounts
      .signTransaction(
        {
          to: param.ToAddress,
          value: amount, //param.Amount,
          gas: 21000,
        },  
        account.privateKey
      )
      .then((signedTransaction) => {
        web3.eth
          .sendSignedTransaction(signedTransaction.rawTransaction)
          .on("error", function (error) {
            data.amount = amount;
            data.message = error;
            data.status = false;
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
     })
     
  } catch (err) {
    data.message = err.message;
    data.status = false;
    res.send(data);
  }
});

app.post("/sendFullTransaction", function (req, res) {
  var data = {};
  var param = req.body;
  try {
    const account = web3.eth.accounts.privateKeyToAccount(param.PrivateKey);
    const address =  account.address;
    web3.eth
    .getBalance(address)
    .then((_balance) => {
      if(_balance < 0.000021){
        data.message = "insuffient balance";
        data.address = address;
        data.status = false;
        res.send(data);
        res.send();
        return;
      }
        var gasPrice = web3.utils.toWei("0.000021",'ether');//gas price
        amount = (parseFloat(_balance) - parseFloat(gasPrice));
        // res.send(amount+"");return;
        
        web3.eth.accounts
        .signTransaction(
          {
            to: param.ToAddress,
            value: amount,
            gas: 21000,
            gasPrice : 1000000000
          },
          account.privateKey
        )
        .then((signedTransaction) => {
          web3.eth
            .sendSignedTransaction(signedTransaction.rawTransaction)
            .on("error", function (error) {
              data.amount = amount;
              data.address = address;
              data.message = error;
              data.status = false;
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
          data.address = address;
          data.status = false;
          res.send(data);
        })
      .catch((_err) => {
        data.message = "balance error"+_err;
        data.address = address;
        data.status = false;
        res.send(data);
      })//balance
    })
    .catch((_err) => {
      data.message = "Provided address is invalid."+_err;
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
