<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Effect extends Model
{
    use HasFactory;

    // Specificeer expliciet de tabelnaam waar dit model aan gekoppeld is.
    // Dit lost de fout op dat de 'event_type_id' kolom niet gevonden wordt in de 'effects' tabel,
    // omdat deze kolom daadwerkelijk in de 'event_effects' tabel bestaat.
    protected $table = 'event_effects';

    // Definieer de kolommen die mass assignment mogen ontvangen.
    // Het is belangrijk dat 'event_type_id' hierin is opgenomen.
    protected $fillable = [
        'event_type_id', // Deze kolom is correct en bestaat in 'event_effects'
        'type',
        'value',
        'is_primary_effect',
        'is_adjacent_effect',
    ];

    /**
     * Definieer de relatie met het EventType model.
     * Een Effect behoort tot één EventType.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function eventType()
    {
        return $this->belongsTo(EventType::class);
    }

    // De 'module()' relatie is verwijderd omdat de 'event_effects' tabel
    // geen 'module_id' kolom heeft volgens de migratiebestanden.
    // Dit voorkomt de SQLSTATE[HY000] fout.
}
