<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TicketSubCategory extends Model
{
    use HasFactory;
    protected $fillable = ['ticket_category_id', 'name'];

    public function category()
    {
        return $this->belongsTo(TicketCategory::class, 'ticket_category_id');
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class, 'ticket_sub_category_id');
    }
}