<?php

declare(strict_types=1);

namespace DigitaldevLx\LaravelInvoiceExpress\Enums;

/**
 * Portuguese AT (Autoridade Tributária) VAT exemption codes.
 *
 * Reference: Portaria n.º 195/2020.
 */
enum VatExemptionCode: string
{
    case M01 = 'M01';
    case M02 = 'M02';
    case M04 = 'M04';
    case M05 = 'M05';
    case M06 = 'M06';
    case M07 = 'M07';
    case M09 = 'M09';
    case M10 = 'M10';
    case M11 = 'M11';
    case M12 = 'M12';
    case M13 = 'M13';
    case M14 = 'M14';
    case M15 = 'M15';
    case M16 = 'M16';
    case M19 = 'M19';
    case M20 = 'M20';
    case M21 = 'M21';
    case M25 = 'M25';
    case M30 = 'M30';
    case M31 = 'M31';
    case M32 = 'M32';
    case M33 = 'M33';
    case M40 = 'M40';
    case M41 = 'M41';
    case M42 = 'M42';
    case M43 = 'M43';
    case M99 = 'M99';

    public function description(): string
    {
        return match ($this) {
            self::M01 => 'Artigo 16.º, n.º 6 do CIVA',
            self::M02 => 'Artigo 6.º do Decreto-Lei n.º 198/90, de 19 de Junho',
            self::M04 => 'Isento - Artigo 13.º do CIVA',
            self::M05 => 'Isento - Artigo 14.º do CIVA',
            self::M06 => 'Isento - Artigo 15.º do CIVA',
            self::M07 => 'Isento - Artigo 9.º do CIVA',
            self::M09 => 'IVA - não confere direito a dedução',
            self::M10 => 'IVA - regime de isenção (Artigo 53.º do CIVA)',
            self::M11 => 'Regime particular do tabaco',
            self::M12 => 'Regime da margem - Agências de viagens',
            self::M13 => 'Regime da margem - Bens em segunda mão',
            self::M14 => 'Regime da margem - Objetos de arte',
            self::M15 => 'Regime da margem - Objetos de coleção e antiguidades',
            self::M16 => 'Isento - Artigo 14.º do RITI',
            self::M19 => 'Outras isenções',
            self::M20 => 'IVA - regime forfetário (Artigo 59.º-D, n.º 2 do CIVA)',
            self::M21 => 'IVA - não confere direito à dedução (ou expressão similar)',
            self::M25 => 'Mercadorias à consignação (Artigo 38.º, n.º 1, alínea a) do CIVA)',
            self::M30 => 'IVA - autoliquidação (Artigo 2.º, n.º 1, alínea i) do CIVA)',
            self::M31 => 'IVA - autoliquidação (Artigo 2.º, n.º 1, alínea j) do CIVA)',
            self::M32 => 'IVA - autoliquidação (Artigo 2.º, n.º 1, alínea l) do CIVA)',
            self::M33 => 'IVA - autoliquidação (Artigo 2.º, n.º 1, alínea m) do CIVA)',
            self::M40 => 'IVA - autoliquidação (Artigo 6.º, n.º 6, alínea a) do CIVA, a contrário)',
            self::M41 => 'IVA - autoliquidação (Artigo 8.º, n.º 3 do RITI)',
            self::M42 => 'IVA - autoliquidação (Decreto-Lei n.º 21/2007, de 29 de Janeiro)',
            self::M43 => 'IVA - autoliquidação (Decreto-Lei n.º 362/99, de 16 de Setembro)',
            self::M99 => 'Não sujeito ou não tributado',
        };
    }
}
