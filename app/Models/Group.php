<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    use HasFactory;

    protected $fillable = [
        'campaign_id',
        'type',
        'mode',
        'name',
        'description',
        'strategy_content',
        'responsible_name',
        'zone',
        'sort_order',
        'is_hidden',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_hidden' => 'boolean',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public static function modeOptions(): array
    {
        return [
            'supporters' => 'Agregar simpatizantes',
            'strategies' => 'Crear estrategias',
        ];
    }

    public static function definitions(): array
    {
        return [
            'campaign_strategy' => [
                'label' => 'Estrategias de campaña',
                'hint' => 'Enumera y organiza las estrategias prioritarias de la campaña.',
                'allows_members' => false,
            ],
            'town_crier' => [
                'label' => 'Pregoneros',
                'hint' => 'Organiza las personas encargadas de difundir el mensaje de campaña.',
                'allows_members' => true,
            ],
            'electoral_witness' => [
                'label' => 'Testigos electorales',
                'hint' => 'Controla quiénes actuarán como testigos y en qué zona apoyarán.',
                'allows_members' => true,
            ],
            'volunteer' => [
                'label' => 'Voluntarios',
                'hint' => 'Agrupa y coordina a los voluntarios disponibles.',
                'allows_members' => true,
            ],
            'interest_topic' => [
                'label' => 'Temas de interés',
                'hint' => 'Enumera temas clave para conectar con la comunidad.',
                'allows_members' => false,
            ],
            'publicity_distribution' => [
                'label' => 'Publicidad de campaña',
                'hint' => 'Registra responsables, zonas y detalles de la distribución publicitaria.',
                'allows_members' => true,
            ],
        ];
    }

    public function campaign()
    {
        return $this->belongsTo(Campaign::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'group_user', 'group_id', 'user_id')
            ->withPivot('role', 'notes')
            ->withTimestamps();
    }

    public function allowsMembers(): bool
    {
        return $this->mode === 'supporters';
    }

    public function typeLabel(): string
    {
        return (string) data_get(self::definitions(), $this->type . '.label', $this->type);
    }

    public function modeLabel(): string
    {
        return (string) data_get(self::modeOptions(), $this->mode, $this->mode);
    }
}
