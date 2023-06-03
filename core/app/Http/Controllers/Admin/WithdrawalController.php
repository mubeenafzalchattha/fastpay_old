<?php

namespace App\Http\Controllers\Admin;

use App\Constants\Status;
use App\Http\Controllers\Controller;
use App\Models\CryptoCurrency;
use App\Models\Transaction;
use App\Models\Wallet;
use App\Models\Withdrawal;
use Illuminate\Http\Request;

class WithdrawalController extends Controller
{
    public function pending()
    {
        $pageTitle      = 'Pending Withdrawals';
        $withdrawalData = $this->withdrawalData('pending');
        $withdrawals    = $withdrawalData['data'];

        return view('admin.withdraw.withdrawals', compact('pageTitle', 'withdrawals'));
    }

    public function approved()
    {
        $pageTitle      = 'Approved Withdrawals';
        $withdrawalData = $this->withdrawalData('approved');
        $withdrawals    = $withdrawalData['data'];

        return view('admin.withdraw.withdrawals', compact('pageTitle', 'withdrawals'));
    }

    public function rejected()
    {
        $pageTitle = 'Rejected Withdrawals';
        $withdrawalData = $this->withdrawalData('rejected');
        $withdrawals = $withdrawalData['data'];

        return view('admin.withdraw.withdrawals', compact('pageTitle', 'withdrawals'));
    }

    public function log()
    {
        $pageTitle = 'Withdrawals Log';
        $withdrawalData = $this->withdrawalData($scope = null);
        $withdrawals = $withdrawalData['data'];

        return view('admin.withdraw.withdrawals', compact('pageTitle', 'withdrawals'));
    }

    protected function withdrawalData($scope = null)
    {
        if ($scope) {
            $withdrawals = Withdrawal::$scope();
        } else {
            $withdrawals = Withdrawal::where('status', '!=', Status::PAYMENT_INITIATE);
        }

        $withdrawals = $withdrawals->searchable(['trx','user:username'])->dateFilter();

        return [
            'data' => $withdrawals->with(['user', 'crypto'])->orderBy('id', 'desc')->paginate(getPaginate()),
        ];
    }

    public function details($id)
    {
        $general = gs();
        $withdrawal = Withdrawal::where('id',$id)->where('status', '!=', Status::PAYMENT_INITIATE)->with(['user', 'crypto'])->firstOrFail();
        $pageTitle = $withdrawal->user->username . ' Withdraw Requested ' . showAmount($withdrawal->amount) . ' ' . $general->cur_text;

        return view('admin.withdraw.detail', compact('pageTitle', 'withdrawal'));
    }

    public function approve(Request $request)
    {
        $request->validate(['id' => 'required|integer']);
        $withdraw = Withdrawal::where('id', $request->id)->where('status', Status::PAYMENT_PENDING)->with('user')->firstOrFail();
        $withdraw->status = Status::PAYMENT_SUCCESS;
        $withdraw->admin_feedback = $request->details;
        $withdraw->save();

        notify($withdraw->user, 'WITHDRAW_APPROVE', [
            'amount' => showAmount($withdraw->amount, 8),
            'payable' => showAmount($withdraw->payable, 8),
            'charge' => showAmount($withdraw->charge, 8),
            'currency' => $withdraw->crypto->code,
            'trx' => $withdraw->trx,
            'admin_details' => $request->details
        ]);

        $notify[] = ['success', 'Withdrawal approved successfully'];
        return to_route('admin.withdraw.pending')->withNotify($notify);
    }

    public function reject(Request $request)
    {
        $request->validate(['id' => 'required|integer']);
        $withdraw = Withdrawal::where('id', $request->id)->where('status', Status::PAYMENT_PENDING)->with('user')->firstOrFail();

        $withdraw->status = Status::PAYMENT_REJECT;
        $withdraw->admin_feedback = $request->details;
        $withdraw->save();

        $user = $withdraw->user;
        $crypto = CryptoCurrency::where('code', $withdraw->crypto->code)->firstOrFail();
        $userBalance = Wallet::where('user_id', $user->id)->where('crypto_currency_id', $crypto->id)->firstOrFail();
        $userBalance->balance += $withdraw->amount;
        $userBalance->save();

        $transaction = new Transaction();
        $transaction->user_id = $withdraw->user_id;
        $transaction->crypto_currency_id = $withdraw->crypto_currency_id;
        $transaction->amount = $withdraw->amount;
        $transaction->post_balance = $userBalance->balance;
        $transaction->charge = 0;
        $transaction->trx_type = '+';
        $transaction->remark = 'withdraw_reject';
        $transaction->details = showAmount($withdraw->amount, 8) . ' ' . $withdraw->crypto->code . ' Refunded from withdrawal rejection';
        $transaction->trx = $withdraw->trx;
        $transaction->save();

        notify($user, 'WITHDRAW_REJECT', [
            'amount' => showAmount($withdraw->amount, 8),
            'payable' => showAmount($withdraw->payable, 8),
            'charge' => showAmount($withdraw->charge, 8),
            'currency' => $withdraw->crypto->code,
            'trx' => $withdraw->trx,
            'post_balance' => showAmount($userBalance->balance, 8),
            'admin_details' => $request->details
        ]);

        $notify[] = ['success', 'Withdrawal rejected successfully'];
        return to_route('admin.withdraw.pending')->withNotify($notify);
    }
}
