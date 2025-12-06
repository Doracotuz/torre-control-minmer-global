<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ffInventoryMovement extends Model
{
    use HasFactory;

    protected $fillable = [
        'ff_product_id',
        'user_id',
        'quantity',
        'reason',
        'client_name',
        'company_name',
        'client_phone',
        'address',
        'locality',
        'delivery_date',
        'surtidor_name',
        'observations',
        'folio',
        'ff_client_id',
        'ff_client_branch_id',
        'ff_sales_channel_id',
        'ff_transport_line_id',
        'order_type',
        'discount_percentage',
        'is_loan_returned',
        'loan_returned_at',
        'evidence_path_1',
        'evidence_path_2',
        'evidence_path_3',
        'status',
        'rejection_reason',
        'approved_by',
        'approved_at',
        'notification_emails',
        'is_backorder',
        'backorder_fulfilled',     
    ];

    protected $casts = [
        'delivery_date' => 'datetime',
        'approved_at' => 'datetime',
        'is_loan_returned' => 'boolean',
        'discount_percentage' => 'decimal:2',
    ];

    public function product()
    {
        return $this->belongsTo(ffProduct::class, 'ff_product_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function client() 
    { 
        return $this->belongsTo(FfClient::class, 'ff_client_id'); 
    }
    public function branch() 
    { 
        return $this->belongsTo(FfClientBranch::class, 'ff_client_branch_id'); 
    }
    public function channel() 
    { 
        return $this->belongsTo(FfSalesChannel::class, 'ff_sales_channel_id'); 
    }
    public function transport() 
    { 
        return $this->belongsTo(FfTransportLine::class, 'ff_transport_line_id'); 
    }
    public function payment() 
    { 
        return $this->belongsTo(FfPaymentCondition::class, 'ff_payment_condition_id'); 
    }

    public function getEvidenceUrl($number)
    {
        $field = "evidence_path_{$number}";
        if ($this->$field) {
            return \Illuminate\Support\Facades\Storage::disk('s3')->url($this->$field);
        }
        return null;
    }
    
    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }    

}