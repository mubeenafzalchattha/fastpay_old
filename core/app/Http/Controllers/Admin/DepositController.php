<?php

namespace App\Http\Controllers\Admin;

use App\Models\Deposit;
use App\Http\Controllers\Controller;

class DepositController extends Controller
{
    public function deposit()
    {
        $pageTitle = 'Deposit History';
        $deposits  = Deposit::searchable(['trx','user:username'])->dateFilter()->orderBy('id','desc')->with(['user', 'crypto'])->paginate(getPaginate());

        return view('admin.deposit.log', compact('pageTitle', 'deposits'));
    }

    public function details($id)
    {
        $general   = gs();
        $deposit   = Deposit::where('id', $id)->with(['user', 'crypto'])->firstOrFail();
        $pageTitle = $deposit->user->username.' Deposit requested ' . showAmount($deposit->amount,8) . ' '.$deposit->crypto->code;

        return view('admin.deposit.detail', compact('pageTitle', 'deposit'));
    }
}
