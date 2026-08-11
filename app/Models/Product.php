<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Product extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'image',
        'price',
        'description',
        'included',
        'payment_info',
        'duration',
        'stock',
        'whatsapp',
        'status',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            // float (bukan decimal:2): konsisten dengan agregasi SUM() di
            // dashboard, dan menghindari konversi tipe pada frontend.
            'price' => 'float',
            'stock' => 'integer',
            'status' => 'boolean',
        ];
    }

    /**
     * Get the category that owns the product.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get the orders for the product.
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Scope: hanya produk yang kategorinya aktif.
     */
    public function scopeFromActiveCategory(Builder $query): Builder
    {
        return $query->whereHas('category', fn ($q) => $q->where('status', true));
    }

    /**
     * Bangun URL WhatsApp dengan pesan tertentu.
     *
     * Nomor selalu berasal dari database (products.whatsapp), tidak pernah
     * di-hard-code di source code.
     */
    public function whatsappUrl(string $message): string
    {
        $phone = preg_replace('/\D/', '', (string) $this->whatsapp);

        return 'https://wa.me/'.$phone.'?text='.rawurlencode($message);
    }

    /**
     * Generate a unique slug from the given name.
     *
     * Appends a numeric suffix (e.g. -1, -2) when the slug already exists.
     */
    public static function generateUniqueSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'layanan';
        $slug = $base;
        $counter = 1;

        while (static::where('slug', $slug)->exists()) {
            $slug = $base.'-'.$counter++;
        }

        return $slug;
    }
}
