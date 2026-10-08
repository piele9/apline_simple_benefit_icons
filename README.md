# APLINE — ikony korzyści dla PrestaShop 9

Blok krótkich informacji przy produkcie: obraz lub ikona, tekst i opcjonalny link. Pozwala przedstawić korzyści i informacje o obsłudze sklepu. Panel oraz komunikaty są po polsku.

## Funkcje

- Wiersze z JPG, PNG, WebP lub ikoną Unicode / encją HTML.
- Kolejność, widoczność, link dla całego wiersza i otwieranie w nowej karcie.
- Konfigurowalny kolor tekstu i miejsce wyświetlania.
- Duży przycisk **Zarządzaj korzyściami**.
- Ukrywanie bloku na produktach wirtualnych.
- Neutralne, oznaczone przykłady na świeżej instalacji — zastąp je rzeczywistymi informacjami sklepu.

## Wymagania

PrestaShop 9 i PHP 8.1+ (zgodny z wymaganiami użytej wersji PrestaShop). Do przesyłania obrazów potrzebne jest prawo zapisu w `views/img/`. Maksymalny rozmiar obrazu: 2 MB.

## Instalacja

1. Pobierz ZIP z [Releases](https://github.com/piele9/apline_simple_benefit_icons/releases).
2. W panelu PrestaShop wybierz **Moduły → Menedżer modułów → Prześlij moduł** i wskaż paczkę.
3. Przy instalacji ręcznej rozpakuj folder `apline_simple_benefit_icons` do `modules/`; nazwa folderu musi być zgodna z nazwą modułu.
4. Zainstaluj moduł i otwórz konfigurację.

## Konfiguracja

1. Ustaw miejsce: strona produktu (blok zaufania, domyślnie `displayProductAdditionalInfo`), lewa/prawa kolumna albo stopka produktu. Widoczność hooka zależy od motywu.
2. Wybierz kolor tekstu (domyślnie czarny) i zapisz.
3. Kliknij **Zarządzaj korzyściami**. Dodaj lub edytuj wiersz: tekst, obraz z tekstem alternatywnym albo ikonę (np. `1F69A` lub `&#x1F69A;`). Obraz ma pierwszeństwo przed ikoną.
4. Opcjonalnie wpisz link, wybierz nową kartę, ustaw widoczność i kolejność. Zastąp przykłady własnymi informacjami.
5. Sprawdź zwykły produkt oraz produkt wirtualny (blok ma być ukryty).

W szablonie Smarty można użyć `{widget name='apline_simple_benefit_icons'}`. Moduł nie zmienia warunków sprzedaży; teksty wpisuje właściciel sklepu.

## Aktualizacja

Zrób kopię bazy i katalogu modułu wraz z obrazami. Prześlij nowy ZIP i uruchom aktualizację w menedżerze. Wersja 1.1.0 zmienia nazwę zakładki i interfejs, zachowując własne teksty, kolor, hooki i obrazy. Przykłady instalacyjne nie nadpisują istniejących wierszy. Nie odinstalowuj w celu aktualizacji; po niej wyczyść cache i sprawdź blok w motywie.

## Odinstalowanie

Odinstalowanie usuwa wiersze, konfigurację i przesłane obrazy. Wykonaj kopię, jeśli chcesz je zachować.

## Historia zmian i licencja

Zmiany: [CHANGELOG.md](CHANGELOG.md). Warunki: [LICENSE.md](LICENSE.md), Custom Attribution License v1.0. Użycie komercyjne, modyfikacja i dystrybucja są dozwolone przy zachowaniu widocznego odnośnika APLINE w konfiguracji.

Autor: **APLINE Arkadiusz Pielechowski** — [apline.pl](https://apline.pl).
