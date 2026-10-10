<?php

namespace Database\Seeders;

use App\Models\Page;
use Illuminate\Database\Seeder;

class PagesSeeder extends Seeder
{
    public function run(): void
    {
        $pages = [
            [
                'slug'          => 'impressum',
                'title'         => 'Impressum',
                'title_en'      => 'Legal Notice',
                'content'       => $this->impressum(),
                'content_en'    => '<p>Please see the German version for our legal notice.</p>',
                'is_active'     => true,
                'show_in_footer' => true,
                'sort_order'    => 10,
            ],
            [
                'slug'          => 'datenschutz',
                'title'         => 'Datenschutzerklärung',
                'title_en'      => 'Privacy Policy',
                'content'       => $this->datenschutz(),
                'content_en'    => '<p>Please see the German version for our privacy policy.</p>',
                'is_active'     => true,
                'show_in_footer' => true,
                'sort_order'    => 20,
            ],
            [
                'slug'          => 'agb',
                'title'         => 'Allgemeine Geschäftsbedingungen',
                'title_en'      => 'Terms & Conditions',
                'content'       => $this->agb(),
                'content_en'    => '<p>Please see the German version for our terms and conditions.</p>',
                'is_active'     => true,
                'show_in_footer' => true,
                'sort_order'    => 30,
            ],
            [
                'slug'          => 'ueber-uns',
                'title'         => 'Über uns',
                'title_en'      => 'About Us',
                'content'       => $this->ueberUns(),
                'content_en'    => $this->aboutUs(),
                'is_active'     => true,
                'show_in_footer' => false,
                'sort_order'    => 5,
            ],
        ];

        foreach ($pages as $data) {
            Page::updateOrCreate(['slug' => $data['slug']], $data);
        }

        $this->command->info('✅ Seiten erstellt: Impressum, Datenschutz, AGB, Über uns');
    }

    private function impressum(): string
    {
        return <<<HTML
        <h2>Angaben gemäß § 5 TMG</h2>
        <p>
            <strong>[Ihr vollständiger Name]</strong><br>
            [Straße und Hausnummer]<br>
            [PLZ] [Ort]<br>
            Deutschland
        </p>

        <h2>Kontakt</h2>
        <p>
            Telefon: [Ihre Telefonnummer]<br>
            E-Mail: [Ihre E-Mail-Adresse]
        </p>

        <h2>Umsatzsteuer-ID</h2>
        <p>
            Umsatzsteuer-Identifikationsnummer gemäß § 27 a Umsatzsteuergesetz:<br>
            [USt-IdNr. falls vorhanden]
        </p>

        <h2>Hinweis zur Differenzbesteuerung</h2>
        <p>
            Der Verkauf erfolgt gemäß § 25a UStG im Rahmen der Differenzbesteuerung.
            Ein gesonderter Ausweis der Umsatzsteuer ist daher nicht möglich.
        </p>

        <h2>Streitschlichtung</h2>
        <p>
            Die Europäische Kommission stellt eine Plattform zur Online-Streitbeilegung (OS) bereit:
            <a href="https://ec.europa.eu/consumers/odr">https://ec.europa.eu/consumers/odr</a>.
        </p>
        <p>
            Wir sind nicht bereit oder verpflichtet, an Streitbeilegungsverfahren vor einer
            Verbraucherschlichtungsstelle teilzunehmen.
        </p>
        HTML;
    }

    private function datenschutz(): string
    {
        return <<<HTML
        <h2>Datenschutzerklärung</h2>

        <h3>1. Verantwortlicher</h3>
        <p>Verantwortlich für die Datenverarbeitung auf dieser Website ist [Ihr Name], [Adresse].</p>

        <h3>2. Erhebung und Speicherung personenbezogener Daten</h3>
        <p>
            Beim Besuch unserer Website werden automatisch Informationen durch den Browser übermittelt.
            Diese umfassen u.a. Browsertyp, Betriebssystem, Referrer-URL, IP-Adresse und Uhrzeit.
        </p>

        <h3>3. Bestelldaten</h3>
        <p>
            Zur Abwicklung Ihrer Bestellung verarbeiten wir: Name, Adresse, E-Mail-Adresse,
            Zahlungsdaten und bestellte Artikel. Diese Daten werden ausschließlich zur
            Auftragsabwicklung genutzt und nach Ablauf der gesetzlichen Aufbewahrungsfristen gelöscht.
        </p>

        <h3>4. Zahlungsdienstleister</h3>
        <p>
            Bei Zahlung per PayPal oder Kreditkarte werden Ihre Zahlungsdaten direkt an den
            jeweiligen Zahlungsdienstleister übermittelt. Wir speichern keine Kreditkartendaten.
        </p>

        <h3>5. Ihre Rechte</h3>
        <p>
            Sie haben das Recht auf Auskunft, Berichtigung, Löschung und Einschränkung der
            Verarbeitung Ihrer personenbezogenen Daten sowie das Recht auf Datenübertragbarkeit.
            Wenden Sie sich dazu an: [E-Mail-Adresse]
        </p>

        <h3>6. Cookies</h3>
        <p>
            Diese Website verwendet ausschließlich technisch notwendige Cookies (Session-Cookie
            für den Warenkorb). Es werden keine Tracking- oder Marketing-Cookies eingesetzt.
        </p>

        <p><em>Stand: [Datum]</em></p>
        HTML;
    }

    private function agb(): string
    {
        return <<<HTML
        <h2>Allgemeine Geschäftsbedingungen</h2>

        <h3>§ 1 Geltungsbereich</h3>
        <p>
            Diese AGB gelten für alle Kaufverträge zwischen [Ihr Name] (nachfolgend „Verkäufer")
            und dem Käufer über die Website antike-stiche.de.
        </p>

        <h3>§ 2 Vertragsschluss</h3>
        <p>
            Die Darstellung der Artikel im Online-Shop stellt kein rechtlich bindendes Angebot dar.
            Durch Klicken auf „Jetzt kaufen" geben Sie eine verbindliche Bestellung ab.
            Die Auftragsbestätigung per E-Mail stellt die Annahme Ihres Angebots dar.
        </p>

        <h3>§ 3 Preise und Zahlungsbedingungen</h3>
        <p>
            Alle Preise verstehen sich inklusive der gesetzlichen Umsatzsteuer, sofern anwendbar.
            Der Verkauf erfolgt gemäß § 25a UStG im Rahmen der Differenzbesteuerung.
        </p>
        <p>Zahlungsmöglichkeiten: PayPal, Kreditkarte (Stripe), Banküberweisung.</p>

        <h3>§ 4 Lieferung und Versand</h3>
        <p>
            Die Artikel werden sorgfältig verpackt und per [Deutsche Post / DHL] versendet.
            Versandkosten werden im Bestellprozess ausgewiesen.
        </p>

        <h3>§ 5 Widerrufsrecht</h3>
        <p>
            Als Verbraucher haben Sie das Recht, binnen 14 Tagen ohne Angabe von Gründen
            diesen Vertrag zu widerrufen. Wenden Sie sich dazu an: [E-Mail-Adresse]
        </p>

        <h3>§ 6 Gewährleistung</h3>
        <p>
            Es gelten die gesetzlichen Gewährleistungsrechte. Da es sich um antike Gegenstände
            handelt, gehören altersbedingte Gebrauchsspuren zur Natur des Artikels und stellen
            keinen Mangel dar, sofern im Angebot beschrieben.
        </p>

        <h3>§ 7 Datenschutz</h3>
        <p>Hinweise zur Datenverarbeitung finden Sie in unserer Datenschutzerklärung.</p>

        <h3>§ 8 Anwendbares Recht</h3>
        <p>Es gilt das Recht der Bundesrepublik Deutschland.</p>

        <p><em>Stand: [Datum]</em></p>
        HTML;
    }

    private function ueberUns(): string
    {
        return <<<HTML
        <h2>Über unser Antiquariat</h2>

        <p>
            Willkommen in unserem Versandantiquariat! Wir sind spezialisiert auf
            antike Druckgrafiken, historische Landkarten, Kupferstiche und seltene
            Buchillustrationen aus mehreren Jahrhunderten.
        </p>

        <p>
            Unser Sortiment umfasst über 13.000 einzigartige Artikel aus ganz Europa.
            Jedes Stück ist ein Original – kein Reproduktionsdruck.
        </p>

        <h3>Unsere Leidenschaft</h3>
        <p>
            Seit vielen Jahren sammeln und verkaufen wir historische Druckgrafiken.
            Unser Ziel ist es, dieses Kulturgut zu erhalten und für Sammler,
            Dekorateure und Liebhaber antiker Kunst zugänglich zu machen.
        </p>

        <h3>Versand und Service</h3>
        <p>
            Alle Artikel werden sorgfältig in stabilen Versandrohren oder zwischen
            Kartonplatten verpackt und weltweit versendet. Bei Fragen helfen wir
            Ihnen gerne weiter – sprechen Sie uns einfach an!
        </p>

        <p>
            <strong>Kontakt:</strong><br>
            E-Mail: info@antike-stiche.de
        </p>
        HTML;
    }

    private function aboutUs(): string
    {
        return <<<HTML
        <h2>About Our Antiquarian Bookshop</h2>

        <p>
            Welcome to our antiquarian shop! We specialize in antique prints, historical maps,
            copper engravings, and rare book illustrations spanning several centuries.
        </p>

        <p>
            Our inventory includes over 13,000 unique original items from across Europe.
            Every piece is an original – no reproductions.
        </p>

        <h3>Our Passion</h3>
        <p>
            For many years, we have been collecting and selling historical prints.
            Our goal is to preserve this cultural heritage and make it accessible
            to collectors, interior designers, and lovers of antique art.
        </p>

        <h3>Shipping & Service</h3>
        <p>
            All items are carefully packed in sturdy postal tubes or between cardboard
            sheets and shipped worldwide. Feel free to contact us with any questions!
        </p>

        <p>
            <strong>Contact:</strong><br>
            Email: info@antike-stiche.de
        </p>
        HTML;
    }
}
