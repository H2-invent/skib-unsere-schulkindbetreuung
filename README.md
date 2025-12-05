# SKiB - Digitale Verwaltung der Schulkindbetreuung

## Online Anmeldung und Verwaltung für die Schulkindbetreuung

Die Schulkindbetreuung bzw. Ganztagsbetreuung von Grundschulkindern ist eine komplexe Dienstleistung. Viele Menschen und Institutionen sind bei der Finanzierung, Planung, Verwaltung und Umsetzung beteiligt. 

Der Schulträger von Morgen stellt eine innovative, moderne und transparente Informationsplattform, Anmeldung und Verwaltung allen Beteiligten zur Verfügung. Digitalisierung und umweltbewusstes Handeln sind zentrale Themen unserer Zeit.

## Jedes Kind hat einen gesetzlichen Anspruch auf eine Ganztagsbetreuung

Ab dem Jahr 2025 haben Eltern einen gesetzlichen Anspruch auf Ganztagsbetreuung für ihre Grundschulkinder. Dieser Rechtsanspruch wird ab 2026 in vollem Umfang umgesetzt.

Allerdings ist die Situation bereits jetzt angespannt, da es an Betreuungspersonal bei den Schulträgern mangelt.

Um diese Herausforderungen zu meistern, bietet SKIB unserer-schulkindbetreuung.de eine innovative Lösung an. Die Plattform ermöglicht es den Trägern, die Organisation und Verwaltung der Ganztagsbetreuung einfach und effizient zu digitalisieren. Dies führt dazu, dass bis zu 80% des Verwaltungsaufwands eingespart werden können.

Das eingesparte Personal kann sich dann auf die wesentlichen Aufgaben konzentrieren: die direkte Arbeit mit den Kindern. Durch diese Entlastung wird nicht nur die Effizienz erhöht, sondern auch die Qualität der Betreuung verbessert. So können die Träger sicherstellen, dass die gesetzlichen Vorgaben zur Ganztagsbetreuung ab 2025 und 2026 erfüllt werden, während gleichzeitig die Bedürfnisse der Kinder in den Mittelpunkt rücken.

# Wir beraten Schulträger bei der Digitalisierung Ihrer Schulkindbetreuung gerne individuell:

* info@unsere-schulkindbetreuung.de
* +49 07621-9515700

# License
Weitere Informationen zur Lizenz finden Sie im License Dokument in diesem Repository.


## Lokale Entwicklung

Für die lokale Entwicklung nutzen wir [DDEV](https://ddev.readthedocs.io/).

### Voraussetzungen

- [Docker](https://www.docker.com/) muss installiert sein
- [DDEV](https://ddev.readthedocs.io/en/stable/users/install/) muss installiert sein

### Installation

1. **Repository klonen:**
   ```bash
   git clone https://github.com/H2-invent/skib-unsere-schulkindbetreuung
   cd skib-unsere-schulkindbetreuung
   ```

2. **DDEV starten:**
   ```bash
   ddev start
   ```

3. **Projekt initialisieren:**
   Nach dem ersten Start muss das Setup ausgeführt werden, um Keycloak und die Datenbank aufzusetzen:
   ```bash
   ddev setup
   ```

### Nützliche DDEV-Befehle

| Befehl          | Beschreibung                                                             |
|-----------------|--------------------------------------------------------------------------|
| `ddev start`    | Startet die DDEV-Umgebung                                                |
| `ddev stop`     | Stoppt die DDEV-Umgebung                                                 |
| `ddev restart`  | Startet die DDEV-Umgebung neu unter Berücksichtigung neuer Konfiguration |
| `ddev ssh`      | Öffnet eine Shell im Web-Container                                       |
| `ddev describe` | Zeigt Informationen zur Umgebung (URLs, Ports, etc.)                     |
| `ddev logs`     | Zeigt die Container-Logs an                                              |

### Zugriff auf die Anwendung

Nach erfolgreichem Start ist die Anwendung unter der von DDEV bereitgestellten URL erreichbar: `https://skib-unsere-schulkindbetreuung.ddev.site`

#### Accounts

- Keycloak Admin: `admin:password`
- Keycloak SKiB Admin: `test@test.com:test`
