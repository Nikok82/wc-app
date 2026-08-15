# Stemmi club mancanti

Generato dal dump `wc_dump_claude.sql` (DB `wc`), 11/08/2026.

## Sintesi

- Club in `awc_clubs`: **1883** — con logo **1342**, senza logo **541**.
- Di quelli **effettivamente usati** in una rosa (`awc_squads.team_past_id`): **538 club senza stemma**, per **1310 righe convocato**.
- I club senza logo e mai usati in una rosa sono la differenza: non urgenti.

## ⚠️ Prima leggi questo

Oggi `awc_clubs.logo` contiene un **URL esterno completo** (`https://cdn.soccerwiki.org/images/logos/clubs/NNN.png`) usato tale e quale come `src`. Sono **1342 hotlink a un sito terzo**: se soccerwiki cambia i percorsi o blocca il referer, spariscono tutti insieme senza preavviso.

Propongo di allineare gli stemmi alla logica già usata per le maglie: **in colonna solo il nome file**, immagine servita da una rotta interna.

- **Cartella**: `resources/images/clubs/`
- **Nome file**: `{club_id}.png` → es. `C-1.png`, `C-274.png` (il `club_id` è già univoco e stabile, non ha accenti né spazi)
- **Rotta**: `/img/clubs/{file}`, gemella di `/img/kits/{anno}/{file}`
- **Formato**: PNG con sfondo trasparente, lato lungo 64 px (a video sono renderizzati a 16 px)

Questa è una decisione tua: se preferisci restare sugli URL esterni per i 1342 già fatti e mettere in locale solo i nuovi, si può — ma resti con due meccanismi diversi nella stessa colonna.

## Elenco — ordinato per nazione, poi per nome

`Conv.` = numero di righe convocato che mostrerebbero lo stemma. `Mondiali` = in quali edizioni compare.

| # | club_id | File da creare | Club | Nazione | Conv. | Mondiali |
|---|---|---|---|---|---|---|
| 1 | `C-1311` | `C-1311.png` | CM Belcourt | Algeria | 1 | 1982 |
| 2 | `C-1278` | `C-1278.png` | GCB Mascara | Algeria | 2 | 1982 1986 |
| 3 | `C-1312` | `C-1312.png` | JH El Djazaïr | Algeria | 1 | 1982 |
| 4 | `C-1275` | `C-1275.png` | JS El Biar | Algeria | 1 | 1986 |
| 5 | `C-1310` | `C-1310.png` | MP Alger | Algeria | 1 | 1982 |
| 6 | `C-1277` | `C-1277.png` | RC Kouba | Algeria | 4 | 1982 1986 |
| 7 | `C-844` | `C-844.png` | Atl. Petróleos Luanda | Angola | 3 | 2006 |
| 8 | `C-843` | `C-843.png` | Aviação | Angola | 2 | 2006 |
| 9 | `C-854` | `C-854.png` | Primeiro de Agosto | Angola | 1 | 2006 |
| 10 | `C-914` | `C-914.png` | Al-Hilal | Arabia Saudita | 16 | 2002 2006 |
| 11 | `C-299` | `C-299.png` | Al-Najma | Arabia Saudita | 1 | 2026 |
| 12 | `C-369` | `C-369.png` | Al-Najma | Arabia Saudita | 1 | 2026 |
| 13 | `C-917` | `C-917.png` | Al-Nasr | Arabia Saudita | 1 | 2006 |
| 14 | `C-1083` | `C-1083.png` | Al-Qadisiya | Arabia Saudita | 1 | 1998 |
| 15 | `C-540` | `C-540.png` | Al-Ra'ed | Arabia Saudita | 1 | 2018 |
| 16 | `C-919` | `C-919.png` | Al-Shabab | Arabia Saudita | 2 | 1994 2006 |
| 17 | `C-496` | `C-496.png` | Al-Wahda | Arabia Saudita | 3 | 1998 2022 |
| 18 | `C-1175` | `C-1175.png` | Ohud | Arabia Saudita | 1 | 1994 |
| 19 | `C-1786` | `C-1786.png` | Club Atlético Estudiantil Porteño | Argentina | 5 | 1930 1934 |
| 20 | `C-1789` | `C-1789.png` | Club Sportivo Alsina | Argentina | 1 | 1934 |
| 21 | `C-1782` | `C-1782.png` | Club Sportivo Buenos Aires | Argentina | 3 | 1930 1934 |
| 22 | `C-706` | `C-706.png` | Colón (SF) | Argentina | 2 | 2010 |
| 23 | `C-1783` | `C-1783.png` | Colón de Santa Fe | Argentina | 1 | 1934 |
| 24 | `C-1020` | `C-1020.png` | River Plate | Argentina | 1 | 2002 |
| 25 | `C-1787` | `C-1787.png` | Sarmiento Chaco | Argentina | 1 | 1934 |
| 26 | `C-1093` | `C-1093.png` | Unión (SF) | Argentina | 2 | 1998 |
| 27 | `C-1788` | `C-1788.png` | Unión de Santa Fe | Argentina | 2 | 1934 |
| 28 | `C-1405` | `C-1405.png` | Footscray JUST | Australia | 2 | 1974 |
| 29 | `C-1403` | `C-1403.png` | Hakoah Sydney | Australia | 2 | 1974 |
| 30 | `C-1399` | `C-1399.png` | Safeway United | Australia | 3 | 1974 |
| 31 | `C-1398` | `C-1398.png` | St. George | Australia | 5 | 1974 |
| 32 | `C-1358` | `C-1358.png` | West Adelaide | Australia | 2 | 1982 |
| 33 | `C-1400` | `C-1400.png` | Western Suburbs (NSW) | Australia | 1 | 1974 |
| 34 | `C-907` | `C-907.png` | Admira Wacker Mödling | Austria | 6 | 1978 1982 1990 2006 |
| 35 | `C-1591` | `C-1591.png` | Semmering | Austria | 1 | 1958 |
| 36 | `C-1182` | `C-1182.png` | Swarovski Tirol | Austria | 5 | 1990 |
| 37 | `C-1058` | `C-1058.png` | Tirol Innsbruck | Austria | 1 | 2002 |
| 38 | `C-1315` | `C-1315.png` | VÖEST Linz | Austria | 3 | 1978 1982 |
| 39 | `C-1589` | `C-1589.png` | Wacker Vienna | Austria | 9 | 1934 1954 1958 |
| 40 | `C-1374` | `C-1374.png` | Wiener SC | Austria | 1 | 1978 |
| 41 | `C-1244` | `C-1244.png` | Beerschot VAC | Belgio | 10 | 1938 1970 1986 |
| 42 | `C-1628` | `C-1628.png` | Charleroi SC | Belgio | 1 | 1954 |
| 43 | `C-889` | `C-889.png` | Club Brugge | Belgio | 3 | 2006 |
| 44 | `C-1627` | `C-1627.png` | Daring Bruxelles | Belgio | 3 | 1930 1954 |
| 45 | `C-1723` | `C-1723.png` | Daring Club de Bruxelles Societe Royale | Belgio | 7 | 1934 1938 |
| 46 | `C-1873` | `C-1873.png` | Excelsior Hasselt | Belgio | 1 | 1930 |
| 47 | `C-1054` | `C-1054.png` | Gent | Belgio | 2 | 2002 |
| 48 | `C-944` | `C-944.png` | Germinal Beerschot | Belgio | 2 | 1998 2002 |
| 49 | `C-1621` | `C-1621.png` | K.V. Oostende | Belgio | 1 | 1954 |
| 50 | `C-1624` | `C-1624.png` | KV Beerschot Antwerpen | Belgio | 5 | 1930 1954 |
| 51 | `C-1874` | `C-1874.png` | Liersche Sportkring | Belgio | 1 | 1930 |
| 52 | `C-1623` | `C-1623.png` | Lierse | Belgio | 3 | 1934 1938 1954 |
| 53 | `C-902` | `C-902.png` | Mons | Belgio | 1 | 2006 |
| 54 | `C-554` | `C-554.png` | Ostenda | Belgio | 1 | 2018 |
| 55 | `C-978` | `C-978.png` | R.E. Mouscron | Belgio | 5 | 1998 2002 |
| 56 | `C-1371` | `C-1371.png` | RAA La Louvière | Belgio | 1 | 1978 |
| 57 | `C-1167` | `C-1167.png` | RC Liégeois | Belgio | 2 | 1994 |
| 58 | `C-1804` | `C-1804.png` | RFC Malinois | Belgio | 3 | 1930 1934 |
| 59 | `C-1806` | `C-1806.png` | Royal FC Brugeois | Belgio | 3 | 1930 1934 |
| 60 | `C-1727` | `C-1727.png` | Royal Olympic Club de Charleroi | Belgio | 1 | 1938 |
| 61 | `C-1163` | `C-1163.png` | RWD Molenbeek | Belgio | 5 | 1930 1954 1978 1994 |
| 62 | `C-1258` | `C-1258.png` | Sérésien | Belgio | 3 | 1982 1986 |
| 63 | `C-1055` | `C-1055.png` | Sint-Truiden | Belgio | 1 | 2002 |
| 64 | `C-1243` | `C-1243.png` | Thor Waterschei | Belgio | 3 | 1982 1986 |
| 65 | `C-1726` | `C-1726.png` | White Star Brüssel | Belgio | 1 | 1938 |
| 66 | `C-1840` | `C-1840.png` | Alianza Oruro | Bolivia | 1 | 1930 |
| 67 | `C-1663` | `C-1663.png` | Ayacucho La Paz | Bolivia | 1 | 1950 |
| 68 | `C-1838` | `C-1838.png` | Calavera La Paz | Bolivia | 1 | 1930 |
| 69 | `C-1665` | `C-1665.png` | CD Ingavi | Bolivia | 1 | 1950 |
| 70 | `C-1664` | `C-1664.png` | Club Jorge Wilstermann | Bolivia | 1 | 1950 |
| 71 | `C-1839` | `C-1839.png` | CS San Jose Oruro | Bolivia | 1 | 1930 |
| 72 | `C-1662` | `C-1662.png` | Ferroviario La Paz | Bolivia | 4 | 1950 |
| 73 | `C-1661` | `C-1661.png` | Litoral | Bolivia | 8 | 1930 1950 |
| 74 | `C-1837` | `C-1837.png` | Oruro Royal | Bolivia | 5 | 1930 |
| 75 | `C-1666` | `C-1666.png` | San Jose de Oruro | Bolivia | 1 | 1950 |
| 76 | `C-1841` | `C-1841.png` | Universitario La Paz | Bolivia | 2 | 1930 |
| 77 | `C-954` | `C-954.png` | Atlético Paranaense | Brasile | 1 | 2002 |
| 78 | `C-1280` | `C-1280.png` | Guarani | Brasile | 2 | 1978 1986 |
| 79 | `C-1451` | `C-1451.png` | Portuguesa | Brasile | 6 | 1954 1958 1962 1970 |
| 80 | `C-953` | `C-953.png` | San Paolo | Brasile | 3 | 2002 |
| 81 | `C-1669` | `C-1669.png` | São Cristóvão | Brasile | 5 | 1930 1938 |
| 82 | `C-1844` | `C-1844.png` | Ypiranga Niterói | Brasile | 1 | 1930 |
| 83 | `C-1237` | `C-1237.png` | Botev Vraca | Bulgaria | 1 | 1986 |
| 84 | `C-1541` | `C-1541.png` | CDNA Sofija | Bulgaria | 7 | 1962 |
| 85 | `C-1236` | `C-1236.png` | Dobrudža | Bulgaria | 1 | 1986 |
| 86 | `C-1425` | `C-1425.png` | FK Etăr | Bulgaria | 1 | 1974 |
| 87 | `C-1088` | `C-1088.png` | Liteks Loveč | Bulgaria | 4 | 1998 |
| 88 | `C-1472` | `C-1472.png` | Spartak Sofia | Bulgaria | 2 | 1966 |
| 89 | `C-1543` | `C-1543.png` | Spartak Sofia | Bulgaria | 2 | 1962 |
| 90 | `C-523` | `C-523.png` | Colombe Sportive | Camerun | 1 | 2022 |
| 91 | `C-527` | `C-527.png` | Cotonsport Garoua | Camerun | 5 | 1998 2010 2014 2022 |
| 92 | `C-1076` | `C-1076.png` | Espoir Ebowola | Camerun | 1 | 1998 |
| 93 | `C-1075` | `C-1075.png` | Olympic Mvolyé | Camerun | 4 | 1994 1998 |
| 94 | `C-1137` | `C-1137.png` | Prévoyance Yaoundé | Camerun | 1 | 1994 |
| 95 | `C-1206` | `C-1206.png` | Union Douala | Camerun | 6 | 1982 1990 |
| 96 | `C-509` | `C-509.png` | CF Montréal | Canada | 6 | 2022 |
| 97 | `C-1262` | `C-1262.png` | Edmonton Brickmen | Canada | 1 | 1986 |
| 98 | `C-1254` | `C-1254.png` | Toronto Blizzard | Canada | 3 | 1982 1986 |
| 99 | `C-1259` | `C-1259.png` | Victoria Athletic Association | Canada | 1 | 1986 |
| 100 | `C-1609` | `C-1609.png` | Baník Handlová | Cecoslovacchia | 1 | 1954 |
| 101 | `C-1561` | `C-1561.png` | Dukla Pardubice | Cecoslovacchia | 1 | 1958 |
| 102 | `C-1452` | `C-1452.png` | FC VSS Košice | Cecoslovacchia | 2 | 1970 |
| 103 | `C-1453` | `C-1453.png` | Jednota Trenčín | Cecoslovacchia | 1 | 1970 |
| 104 | `C-1612` | `C-1612.png` | Krídla vlasti Olomouc | Cecoslovacchia | 3 | 1954 |
| 105 | `C-1520` | `C-1520.png` | RH Brno | Cecoslovacchia | 1 | 1962 |
| 106 | `C-1679` | `C-1679.png` | SK Židenice | Cecoslovacchia | 3 | 1934 1938 |
| 107 | `C-1562` | `C-1562.png` | Slavia Karlovy Vary | Cecoslovacchia | 1 | 1958 |
| 108 | `C-1613` | `C-1613.png` | Tankista Praga | Cecoslovacchia | 1 | 1954 |
| 109 | `C-1748` | `C-1748.png` | Teplitzer FK | Cecoslovacchia | 1 | 1934 |
| 110 | `C-1825` | `C-1825.png` | La Cruz Valparaiso | Cile | 2 | 1930 |
| 111 | `C-559` | `C-559.png` | Dalian Yifang | Cina | 2 | 2018 |
| 112 | `C-608` | `C-608.png` | Guangzhou E. | Cina | 2 | 2014 2018 |
| 113 | `C-693` | `C-693.png` | Guangzhou R&F | Cina | 1 | 2014 |
| 114 | `C-578` | `C-578.png` | Hebei CFFC | Cina | 1 | 2018 |
| 115 | `C-664` | `C-664.png` | Q. Zhongneng | Cina | 1 | 2014 |
| 116 | `C-964` | `C-964.png` | Shandong Luneng | Cina | 3 | 2002 |
| 117 | `C-967` | `C-967.png` | Shanghai Zhongyuan | Cina | 1 | 2002 |
| 118 | `C-965` | `C-965.png` | Shenzhen Pingan | Cina | 1 | 2002 |
| 119 | `C-962` | `C-962.png` | Sichuan Guancheng | Cina | 1 | 2002 |
| 120 | `C-591` | `C-591.png` | Tianjin Teda | Cina | 1 | 2018 |
| 121 | `C-963` | `C-963.png` | Tianjin Teda | Cina | 2 | 2002 |
| 122 | `C-968` | `C-968.png` | Yunnan Hongta | Cina | 1 | 2002 |
| 123 | `C-1215` | `C-1215.png` | Atlético Junior | Colombia | 1 | 1990 |
| 124 | `C-568` | `C-568.png` | Atlético Junior | Colombia | 8 | 1990 1994 1998 2018 |
| 125 | `C-1477` | `C-1477.png` | 8 August | Corea del Nord | 2 | 1966 |
| 126 | `C-760` | `C-760.png` | Amnokgang | Corea del Nord | 7 | 1966 2010 |
| 127 | `C-1475` | `C-1475.png` | Kigwancha | Corea del Nord | 4 | 1966 |
| 128 | `C-765` | `C-765.png` | Kyonggongopsong | Corea del Nord | 1 | 2010 |
| 129 | `C-1476` | `C-1476.png` | Moranbong | Corea del Nord | 5 | 1966 |
| 130 | `C-763` | `C-763.png` | Rimyongsu | Corea del Nord | 3 | 2010 |
| 131 | `C-761` | `C-761.png` | Sobaeksu | Corea del Nord | 2 | 2010 |
| 132 | `C-991` | `C-991.png` | Anyang LG Cheetahs | Corea del Sud | 2 | 2002 |
| 133 | `C-606` | `C-606.png` | Asan Mugunghwa | Corea del Sud | 1 | 2018 |
| 134 | `C-1242` | `C-1242.png` | Chosun University | Corea del Sud | 1 | 1986 |
| 135 | `C-898` | `C-898.png` | Chunnam Dragons | Corea del Sud | 1 | 2006 |
| 136 | `C-990` | `C-990.png` | Chunnam Dragons | Corea del Sud | 2 | 2002 |
| 137 | `C-1152` | `C-1152.png` | Daewoo Royals | Corea del Sud | 3 | 1986 1990 1994 |
| 138 | `C-897` | `C-897.png` | Gwangju Sangmu Phoenix | Corea del Sud | 1 | 2006 |
| 139 | `C-1239` | `C-1239.png` | Hallelujah | Corea del Sud | 1 | 1986 |
| 140 | `C-1241` | `C-1241.png` | Hanyang University | Corea del Sud | 1 | 1986 |
| 141 | `C-1240` | `C-1240.png` | Korea University | Corea del Sud | 1 | 1986 |
| 142 | `C-995` | `C-995.png` | Korea University | Corea del Sud | 2 | 1990 2002 |
| 143 | `C-1222` | `C-1222.png` | Kunkook University | Corea del Sud | 1 | 1990 |
| 144 | `C-1153` | `C-1153.png` | Kyung Hee University | Corea del Sud | 1 | 1994 |
| 145 | `C-1607` | `C-1607.png` | Pusan University | Corea del Sud | 1 | 1954 |
| 146 | `C-894` | `C-894.png` | Seongnam Ilhwa Chunma | Corea del Sud | 7 | 1994 2006 |
| 147 | `C-1605` | `C-1605.png` | Seoul Army Club | Corea del Sud | 16 | 1954 |
| 148 | `C-896` | `C-896.png` | Ulsan Hyundai Horangi | Corea del Sud | 5 | 1990 1994 2006 |
| 149 | `C-989` | `C-989.png` | Ulsan Hyundai Horangi | Corea del Sud | 2 | 2002 |
| 150 | `C-1606` | `C-1606.png` | Wonjoo | Corea del Sud | 1 | 1954 |
| 151 | `C-1608` | `C-1608.png` | Wonsan University | Corea del Sud | 1 | 1954 |
| 152 | `C-1221` | `C-1221.png` | Yukong Kokkiri | Corea del Sud | 3 | 1986 1990 |
| 153 | `C-1164` | `C-1164.png` | Africa Sports | Costa d'Avorio | 2 | 1982 1994 |
| 154 | `C-644` | `C-644.png` | Séwé Sports | Costa d'Avorio | 1 | 2014 |
| 155 | `C-1211` | `C-1211.png` | Limonense | Costa Rica | 1 | 1990 |
| 156 | `C-1212` | `C-1212.png` | Municipal Punt. | Costa Rica | 1 | 1990 |
| 157 | `C-1210` | `C-1210.png` | Ramonense | Costa Rica | 1 | 1990 |
| 158 | `C-635` | `C-635.png` | Lokomotiva Zagabria | Croazia | 1 | 2014 |
| 159 | `C-1685` | `C-1685.png` | CD Centro Gallego | Cuba | 4 | 1938 |
| 160 | `C-1686` | `C-1686.png` | CD Puentes Grandes | Cuba | 2 | 1938 |
| 161 | `C-1683` | `C-1683.png` | Fortuna Havana | Cuba | 1 | 1938 |
| 162 | `C-1687` | `C-1687.png` | Iberia Havana | Cuba | 3 | 1938 |
| 163 | `C-1684` | `C-1684.png` | Juventud Asturiana | Cuba | 3 | 1938 |
| 164 | `C-1082` | `C-1082.png` | AB | Danimarca | 1 | 1998 |
| 165 | `C-1289` | `C-1289.png` | Brønshøj | Danimarca | 1 | 1986 |
| 166 | `C-807` | `C-807.png` | Copenaghen | Danimarca | 3 | 2006 |
| 167 | `C-1033` | `C-1033.png` | Copenhagen | Danimarca | 2 | 2002 |
| 168 | `C-1288` | `C-1288.png` | KB | Danimarca | 1 | 1986 |
| 169 | `C-660` | `C-660.png` | El Nacional | Ecuador | 10 | 2002 2006 2014 |
| 170 | `C-1799` | `C-1799.png` | Al-Olympi Alexandria | Egitto | 2 | 1934 |
| 171 | `C-1801` | `C-1801.png` | Cairo Shourta Police | Egitto | 1 | 1934 |
| 172 | `C-1227` | `C-1227.png` | El-Olympi | Egitto | 1 | 1990 |
| 173 | `C-1226` | `C-1226.png` | Ghazl El-Mehalla | Egitto | 1 | 1990 |
| 174 | `C-1803` | `C-1803.png` | Union Recreation Ithad | Egitto | 1 | 1934 |
| 175 | `C-1800` | `C-1800.png` | Zamalek Mokhtalat | Egitto | 6 | 1934 |
| 176 | `C-1441` | `C-1441.png` | Adler San Nicolas | El Salvador | 1 | 1970 |
| 177 | `C-1438` | `C-1438.png` | Alianza | El Salvador | 4 | 1970 |
| 178 | `C-1326` | `C-1326.png` | Alianza F.C. | El Salvador | 2 | 1982 |
| 179 | `C-1323` | `C-1323.png` | C.D. Águila | El Salvador | 1 | 1982 |
| 180 | `C-1324` | `C-1324.png` | C.D. FAS | El Salvador | 3 | 1982 |
| 181 | `C-1321` | `C-1321.png` | C.D. Platense Municipal | El Salvador | 2 | 1982 |
| 182 | `C-1322` | `C-1322.png` | C.D. Santiagueño | El Salvador | 5 | 1982 |
| 183 | `C-1216` | `C-1216.png` | Al-Khaleej | Emirati Arabi Uniti | 3 | 1990 |
| 184 | `C-1218` | `C-1218.png` | Al-Nasr | Emirati Arabi Uniti | 1 | 1990 |
| 185 | `C-858` | `C-858.png` | Al-Sharjah | Emirati Arabi Uniti | 1 | 2006 |
| 186 | `C-364` | `C-364.png` | Dibba Al-Fujairah | Emirati Arabi Uniti | 2 | 2026 |
| 187 | `C-1749` | `C-1749.png` | AC Nimes | Francia | 1 | 1934 |
| 188 | `C-1771` | `C-1771.png` | Amiens | Francia | 1 | 1934 |
| 189 | `C-1831` | `C-1831.png` | Amiens | Francia | 2 | 1930 |
| 190 | `C-457` | `C-457.png` | Amiens | Francia | 2 | 2018 2022 |
| 191 | `C-1828` | `C-1828.png` | Antibes | Francia | 1 | 1930 |
| 192 | `C-925` | `C-925.png` | Bastia | Francia | 2 | 2002 2006 |
| 193 | `C-904` | `C-904.png` | Brest | Francia | 2 | 2006 |
| 194 | `C-1830` | `C-1830.png` | CASG | Francia | 1 | 1930 |
| 195 | `C-1202` | `C-1202.png` | Créteil-Lusitanos | Francia | 1 | 1990 |
| 196 | `C-625` | `C-625.png` | Digione | Francia | 2 | 2018 |
| 197 | `C-1832` | `C-1832.png` | Excelsior AC Roubaix | Francia | 1 | 1930 |
| 198 | `C-1774` | `C-1774.png` | Excelsior Roubaix | Francia | 1 | 1934 |
| 199 | `C-610` | `C-610.png` | Guingamp | Francia | 1 | 2018 |
| 200 | `C-910` | `C-910.png` | Guingamp | Francia | 1 | 2006 |
| 201 | `C-1205` | `C-1205.png` | La Roche | Francia | 1 | 1990 |
| 202 | `C-913` | `C-913.png` | Moulinoise | Francia | 1 | 2006 |
| 203 | `C-831` | `C-831.png` | Nizza | Francia | 1 | 2006 |
| 204 | `C-908` | `C-908.png` | Poirè | Francia | 1 | 2006 |
| 205 | `C-1690` | `C-1690.png` | Roubaix | Francia | 2 | 1934 1938 |
| 206 | `C-1689` | `C-1689.png` | Sète | Francia | 6 | 1930 1934 1938 |
| 207 | `C-1465` | `C-1465.png` | Stade Français | Francia | 1 | 1966 |
| 208 | `C-1516` | `C-1516.png` | Stade Français | Francia | 2 | 1962 |
| 209 | `C-1301` | `C-1301.png` | Stade Quimpérois | Francia | 1 | 1982 |
| 210 | `C-920` | `C-920.png` | Strasburgo | Francia | 2 | 2006 |
| 211 | `C-1463` | `C-1463.png` | Tolosa | Francia | 1 | 1966 |
| 212 | `C-454` | `C-454.png` | Troyes | Francia | 5 | 1974 1994 2018 2022 |
| 213 | `C-826` | `C-826.png` | Troyes | Francia | 3 | 2002 2006 |
| 214 | `C-1700` | `C-1700.png` | Admira Vienna | Germania | 5 | 1934 1938 |
| 215 | `C-1141` | `C-1141.png` | Bayer Uerdingen | Germania | 3 | 1982 1986 1994 |
| 216 | `C-727` | `C-727.png` | Coblenza | Germania | 2 | 1938 2010 |
| 217 | `C-171` | `C-171.png` | Cosmos Koblenz | Germania | 1 | 2026 |
| 218 | `C-887` | `C-887.png` | Dynamo Dresda | Germania | 1 | 2006 |
| 219 | `C-487` | `C-487.png` | Hertha Berlino | Germania | 19 | 1970 1978 1998 2002 2006 2010 2014 2018 2022 |
| 220 | `C-880` | `C-880.png` | Magonza 05 | Germania | 1 | 2006 |
| 221 | `C-1755` | `C-1755.png` | Rot-Weiss Francoforte | Germania | 1 | 1934 |
| 222 | `C-1756` | `C-1756.png` | Schwaben Augsburg | Germania | 1 | 1934 |
| 223 | `C-1699` | `C-1699.png` | Speldorf | Germania | 1 | 1938 |
| 224 | `C-1758` | `C-1758.png` | SpVgg Viktoria Amburgo | Germania | 1 | 1934 |
| 225 | `C-1760` | `C-1760.png` | SV Union Hamborn | Germania | 1 | 1934 |
| 226 | `C-1751` | `C-1751.png` | TuS Duisburg | Germania | 1 | 1934 |
| 227 | `C-1750` | `C-1750.png` | VfB Speldorf | Germania | 1 | 1934 |
| 228 | `C-1753` | `C-1753.png` | VfL Benrath | Germania | 1 | 1934 |
| 229 | `C-1759` | `C-1759.png` | Wacker Monaco | Germania | 1 | 1934 |
| 230 | `C-1395` | `C-1395.png` | Magdeburgo | Germania Est | 4 | 1974 |
| 231 | `C-1391` | `C-1391.png` | Sachsenring Zwickau | Germania Est | 1 | 1974 |
| 232 | `C-1397` | `C-1397.png` | Viktoria Francoforte | Germania Est | 1 | 1974 |
| 233 | `C-1553` | `C-1553.png` | Düren 99 | Germania Ovest | 1 | 1958 |
| 234 | `C-1549` | `C-1549.png` | Rot-Weiss Essen | Germania Ovest | 4 | 1954 1958 |
| 235 | `C-1552` | `C-1552.png` | SV Sodingen | Germania Ovest | 1 | 1958 |
| 236 | `C-1551` | `C-1551.png` | SV Wuppertal | Germania Ovest | 1 | 1958 |
| 237 | `C-1496` | `C-1496.png` | Westfalia Herne | Germania Ovest | 1 | 1962 |
| 238 | `C-743` | `C-743.png` | Bechem Chelsea | Ghana | 1 | 2010 |
| 239 | `C-881` | `C-881.png` | Great Olympics | Ghana | 1 | 2006 |
| 240 | `C-878` | `C-878.png` | King Faisal Babes | Ghana | 1 | 2006 |
| 241 | `C-1123` | `C-1123.png` | Constant Spring | Giamaica | 1 | 1998 |
| 242 | `C-1118` | `C-1118.png` | Galaxy United | Giamaica | 1 | 1998 |
| 243 | `C-1120` | `C-1120.png` | Olympic Gardens | Giamaica | 1 | 1998 |
| 244 | `C-1116` | `C-1116.png` | Violet Kickers | Giamaica | 1 | 1998 |
| 245 | `C-1125` | `C-1125.png` | Wadadah | Giamaica | 2 | 1998 |
| 246 | `C-1064` | `C-1064.png` | Yokohama Flügels | Giappone | 3 | 1998 |
| 247 | `C-388` | `C-388.png` | Al-Faisaly | Giordania | 3 | 2026 |
| 248 | `C-703` | `C-703.png` | Levadiakos | Grecia | 1 | 2010 |
| 249 | `C-1159` | `C-1159.png` | OFI Creta | Grecia | 2 | 1994 |
| 250 | `C-971` | `C-971.png` | OFI Creta | Grecia | 1 | 2002 |
| 251 | `C-834` | `C-834.png` | Olympiakos | Grecia | 2 | 2006 |
| 252 | `C-1074` | `C-1074.png` | Panachaïkī | Grecia | 1 | 1998 |
| 253 | `C-1027` | `C-1027.png` | PAOK Salonicco | Grecia | 1 | 2002 |
| 254 | `C-753` | `C-753.png` | Skoda Xanthī | Grecia | 1 | 2010 |
| 255 | `C-776` | `C-776.png` | Comunicaciones | Guatemala | 1 | 2006 |
| 256 | `C-1432` | `C-1432.png` | Aigle Noir | Haiti | 3 | 1974 |
| 257 | `C-1430` | `C-1430.png` | Victory FC | Haiti | 1 | 1974 |
| 258 | `C-1340` | `C-1340.png` | CD Universidad | Honduras | 3 | 1982 |
| 259 | `C-1342` | `C-1342.png` | Platense | Honduras | 2 | 1982 |
| 260 | `C-771` | `C-771.png` | Platense | Honduras | 2 | 1994 2010 |
| 261 | `C-666` | `C-666.png` | Real Sociedad | Honduras | 1 | 2014 |
| 262 | `C-1539` | `C-1539.png` | Burnley F.C. | Inghilterra | 3 | 1958 1962 |
| 263 | `C-1282` | `C-1282.png` | Bury | Inghilterra | 1 | 1986 |
| 264 | `C-456` | `C-456.png` | QPR | Inghilterra | 8 | 1986 1990 2014 2018 2022 |
| 265 | `C-1540` | `C-1540.png` | Sunderland F.C. | Inghilterra | 1 | 1962 |
| 266 | `C-861` | `C-861.png` | Abu Moslem | Iran | 1 | 2006 |
| 267 | `C-1098` | `C-1098.png` | Bahman | Iran | 4 | 1998 |
| 268 | `C-1382` | `C-1382.png` | Homa F.C. | Iran | 2 | 1978 |
| 269 | `C-1385` | `C-1385.png` | Malavan F.C. | Iran | 1 | 1978 |
| 270 | `C-676` | `C-676.png` | Naft Teheran | Iran | 1 | 2014 |
| 271 | `C-860` | `C-860.png` | Pas | Iran | 3 | 2006 |
| 272 | `C-1380` | `C-1380.png` | Pas F.C. | Iran | 4 | 1978 |
| 273 | `C-1100` | `C-1100.png` | Polyacryl Esfahan | Iran | 1 | 1998 |
| 274 | `C-1381` | `C-1381.png` | Rastakhiz F.C. | Iran | 1 | 1978 |
| 275 | `C-857` | `C-857.png` | Saba Battery | Iran | 3 | 2006 |
| 276 | `C-1384` | `C-1384.png` | Sepahan F.C. | Iran | 2 | 1978 |
| 277 | `C-1378` | `C-1378.png` | Shahbaz | Iran | 4 | 1978 |
| 278 | `C-1099` | `C-1099.png` | Shahrdari Tabriz | Iran | 1 | 1998 |
| 279 | `C-1379` | `C-1379.png` | Taj | Iran | 4 | 1978 |
| 280 | `C-1248` | `C-1248.png` | Al Shabab Baghdad | Iraq | 4 | 1986 |
| 281 | `C-1246` | `C-1246.png` | Al-Rasheed | Iraq | 8 | 1986 |
| 282 | `C-680` | `C-680.png` | Ashdod | Israele | 1 | 2014 |
| 283 | `C-874` | `C-874.png` | Ashdod | Israele | 1 | 2006 |
| 284 | `C-1450` | `C-1450.png` | Hakoah Ramat Gan | Israele | 1 | 1970 |
| 285 | `C-594` | `C-594.png` | Hapoel Be'er Sheva | Israele | 2 | 2014 2018 |
| 286 | `C-709` | `C-709.png` | Hapoel Petah Tiqwa | Israele | 3 | 1970 2010 |
| 287 | `C-767` | `C-767.png` | Maccabi Natanya | Israele | 3 | 1970 2010 |
| 288 | `C-1449` | `C-1449.png` | Shimshon Tel Aviv | Israele | 1 | 1970 |
| 289 | `C-1509` | `C-1509.png` | A.C. Mantova | Italia | 2 | 1962 |
| 290 | `C-1508` | `C-1508.png` | Atalanta B.C. | Italia | 4 | 1950 1958 1962 |
| 291 | `C-1364` | `C-1364.png` | L.R. Vicenza | Italia | 1 | 1978 |
| 292 | `C-1510` | `C-1510.png` | Torino F.C. | Italia | 3 | 1950 1962 |
| 293 | `C-1039` | `C-1039.png` | Vicenza | Italia | 1 | 2002 |
| 294 | `C-1848` | `C-1848.png` | ASK Belgrado | Jugoslavia | 2 | 1930 |
| 295 | `C-1847` | `C-1847.png` | Jugoslavija Belgrado | Jugoslavia | 3 | 1930 |
| 296 | `C-1480` | `C-1480.png` | Vojvodina Novi Sad | Jugoslavia | 9 | 1954 1958 1962 |
| 297 | `C-1409` | `C-1409.png` | Željezničar | Jugoslavia | 2 | 1974 |
| 298 | `C-1334` | `C-1334.png` | Al Kuwait Kaifan | Kuwait | 4 | 1982 |
| 299 | `C-1338` | `C-1338.png` | Al Qadisiya Kuwait | Kuwait | 4 | 1982 |
| 300 | `C-1336` | `C-1336.png` | Al-Shalmiya | Kuwait | 1 | 1982 |
| 301 | `C-1335` | `C-1335.png` | Al-Tadhamon | Kuwait | 3 | 1982 |
| 302 | `C-1296` | `C-1296.png` | CODM Meknès | Marocco | 1 | 1986 |
| 303 | `C-1177` | `C-1177.png` | Kawkab Marrakech | Marocco | 6 | 1986 1994 |
| 304 | `C-1293` | `C-1293.png` | Maghreb Fès | Marocco | 3 | 1970 1986 |
| 305 | `C-1297` | `C-1297.png` | Maghreb Fez | Marocco | 1 | 1986 |
| 306 | `C-1179` | `C-1179.png` | Mouloudia Oujda | Marocco | 2 | 1970 1994 |
| 307 | `C-937` | `C-937.png` | Ol. Khouribga | Marocco | 2 | 1994 2002 |
| 308 | `C-1178` | `C-1178.png` | Olympique Casablanca | Marocco | 1 | 1994 |
| 309 | `C-1460` | `C-1460.png` | Raja Casablanca | Marocco | 3 | 1970 |
| 310 | `C-1066` | `C-1066.png` | RS Settat | Marocco | 3 | 1970 1998 |
| 311 | `C-1461` | `C-1461.png` | Union Sidi Kacem | Marocco | 1 | 1970 |
| 312 | `C-1180` | `C-1180.png` | WAC Casablanca | Marocco | 1 | 1994 |
| 313 | `C-518` | `C-518.png` | Wydad Casablanca | Marocco | 8 | 1970 1986 1994 1998 2022 |
| 314 | `C-1639` | `C-1639.png` | Asturias | Messico | 1 | 1950 |
| 315 | `C-1584` | `C-1584.png` | CD Cuautla | Messico | 1 | 1958 |
| 316 | `C-1526` | `C-1526.png` | CD Zacatapec | Messico | 4 | 1950 1958 1962 |
| 317 | `C-1467` | `C-1467.png` | CD Zacatepec | Messico | 2 | 1954 1966 |
| 318 | `C-865` | `C-865.png` | Chivas Guadalajara | Messico | 27 | 1950 1954 1958 1962 1970 2002 2006 |
| 319 | `C-799` | `C-799.png` | Jaguares | Messico | 1 | 2006 |
| 320 | `C-1593` | `C-1593.png` | Marte | Messico | 2 | 1954 |
| 321 | `C-1641` | `C-1641.png` | Marte | Messico | 2 | 1950 |
| 322 | `C-1835` | `C-1835.png` | Marte FC | Messico | 3 | 1930 |
| 323 | `C-1466` | `C-1466.png` | Oro | Messico | 4 | 1954 1966 |
| 324 | `C-1528` | `C-1528.png` | Oro | Messico | 4 | 1954 1962 |
| 325 | `C-1640` | `C-1640.png` | Oro Guadalajara | Messico | 1 | 1950 |
| 326 | `C-1636` | `C-1636.png` | Real Club España | Messico | 3 | 1950 |
| 327 | `C-1637` | `C-1637.png` | San Sebastian Leon | Messico | 1 | 1950 |
| 328 | `C-1049` | `C-1049.png` | Santos | Messico | 2 | 2002 |
| 329 | `C-1596` | `C-1596.png` | Tampico | Messico | 1 | 1954 |
| 330 | `C-868` | `C-868.png` | UAG Tecos | Messico | 1 | 2006 |
| 331 | `C-1026` | `C-1026.png` | Gabros International | Nigeria | 1 | 2002 |
| 332 | `C-651` | `C-651.png` | Aalesund | Norvegia | 1 | 2014 |
| 333 | `C-1718` | `C-1718.png` | Hardy FK | Norvegia | 1 | 1938 |
| 334 | `C-1714` | `C-1714.png` | Mjøndalen IF | Norvegia | 3 | 1938 |
| 335 | `C-1717` | `C-1717.png` | Odd Grenland BK | Norvegia | 2 | 1938 |
| 336 | `C-1715` | `C-1715.png` | Sarpsborg FK | Norvegia | 1 | 1938 |
| 337 | `C-645` | `C-645.png` | Stabæk | Norvegia | 1 | 2014 |
| 338 | `C-716` | `C-716.png` | Start | Norvegia | 2 | 1994 2010 |
| 339 | `C-1721` | `C-1721.png` | Storm BK | Norvegia | 1 | 1938 |
| 340 | `C-1352` | `C-1352.png` | Gisborne City | Nuova Zelanda | 3 | 1982 |
| 341 | `C-1360` | `C-1360.png` | Gisborne City | Nuova Zelanda | 2 | 1982 |
| 342 | `C-1354` | `C-1354.png` | Invercargill Thistle | Nuova Zelanda | 1 | 1982 |
| 343 | `C-1351` | `C-1351.png` | Mount Wellington | Nuova Zelanda | 2 | 1982 |
| 344 | `C-1356` | `C-1356.png` | North Shore Utd | Nuova Zelanda | 2 | 1982 |
| 345 | `C-1775` | `C-1775.png` | AC Zwolle | Paesi Bassi | 1 | 1934 |
| 346 | `C-1740` | `C-1740.png` | Djocoja | Paesi Bassi | 2 | 1938 |
| 347 | `C-1728` | `C-1728.png` | DWS Amsterdam | Paesi Bassi | 1 | 1938 |
| 348 | `C-1745` | `C-1745.png` | Excelsior Soerabaja | Paesi Bassi | 1 | 1938 |
| 349 | `C-1415` | `C-1415.png` | FC Amsterdam | Paesi Bassi | 1 | 1974 |
| 350 | `C-1776` | `C-1776.png` | HBS Den Haag | Paesi Bassi | 1 | 1934 |
| 351 | `C-1741` | `C-1741.png` | HBS Soerabaja | Paesi Bassi | 4 | 1938 |
| 352 | `C-1738` | `C-1738.png` | Hercules Batavia | Paesi Bassi | 2 | 1938 |
| 353 | `C-1744` | `C-1744.png` | Jong Ambon Batavia | Paesi Bassi | 2 | 1938 |
| 354 | `C-1778` | `C-1778.png` | KFC Koog | Paesi Bassi | 1 | 1934 |
| 355 | `C-1779` | `C-1779.png` | Longa Tilburg | Paesi Bassi | 1 | 1934 |
| 356 | `C-1735` | `C-1735.png` | Neptunus Rotterdam | Paesi Bassi | 3 | 1934 1938 |
| 357 | `C-1780` | `C-1780.png` | Quick Den Haag | Paesi Bassi | 1 | 1934 |
| 358 | `C-1732` | `C-1732.png` | Quick Groningen | Paesi Bassi | 2 | 1938 |
| 359 | `C-1734` | `C-1734.png` | RVV Rormond | Paesi Bassi | 1 | 1938 |
| 360 | `C-1742` | `C-1742.png` | Sparta Batavia | Paesi Bassi | 1 | 1938 |
| 361 | `C-1743` | `C-1743.png` | SVB Batavia | Paesi Bassi | 1 | 1938 |
| 362 | `C-1747` | `C-1747.png` | SVV Semerang | Paesi Bassi | 1 | 1938 |
| 363 | `C-1739` | `C-1739.png` | Tiong Hoa Soerabaja | Paesi Bassi | 3 | 1938 |
| 364 | `C-1736` | `C-1736.png` | Unitas Gorinchem | Paesi Bassi | 2 | 1934 1938 |
| 365 | `C-1737` | `C-1737.png` | Vios Batavia | Paesi Bassi | 1 | 1938 |
| 366 | `C-1730` | `C-1730.png` | VUC Den Haag | Paesi Bassi | 1 | 1938 |
| 367 | `C-1777` | `C-1777.png` | Xerxes Rotterdam | Paesi Bassi | 1 | 1934 |
| 368 | `C-620` | `C-620.png` | Chorrillo | Panama | 1 | 2018 |
| 369 | `C-1092` | `C-1092.png` | Cerro Corá | Paraguay | 1 | 1998 |
| 370 | `C-1658` | `C-1658.png` | Club Presidente Hayes | Paraguay | 2 | 1930 1950 |
| 371 | `C-1250` | `C-1250.png` | Guaraní | Paraguay | 11 | 1930 1950 1958 1986 |
| 372 | `C-656` | `C-656.png` | Libertad | Paraguay | 23 | 1930 1950 1958 1986 2002 2006 2010 2014 |
| 373 | `C-1251` | `C-1251.png` | Sol de América | Paraguay | 5 | 1958 1986 |
| 374 | `C-1305` | `C-1305.png` | Atlético Chalaco | Perù | 2 | 1930 1982 |
| 375 | `C-1852` | `C-1852.png` | Italiano Lima | Perù | 1 | 1930 |
| 376 | `C-569` | `C-569.png` | Universitario | Perù | 12 | 1970 1982 2018 |
| 377 | `C-1707` | `C-1707.png` | Dab Katowice | Polonia | 1 | 1938 |
| 378 | `C-1428` | `C-1428.png` | Gwardia Varsavia | Polonia | 1 | 1974 |
| 379 | `C-1705` | `C-1705.png` | KS Warszawianka | Polonia | 1 | 1938 |
| 380 | `C-1712` | `C-1712.png` | Naprzód Lipiny | Polonia | 2 | 1938 |
| 381 | `C-1713` | `C-1713.png` | Pogon Lwów | Polonia | 1 | 1938 |
| 382 | `C-982` | `C-982.png` | RKS Radomsko | Polonia | 1 | 2002 |
| 383 | `C-1706` | `C-1706.png` | Śląsk Świętochłowice | Polonia | 1 | 1938 |
| 384 | `C-726` | `C-726.png` | Wisła Cracovia | Polonia | 14 | 1974 1978 1982 2002 2010 |
| 385 | `C-788` | `C-788.png` | Wisła Cracovia | Polonia | 4 | 2006 |
| 386 | `C-794` | `C-794.png` | Wisła Płock | Polonia | 1 | 2006 |
| 387 | `C-734` | `C-734.png` | Zagłębie Lubin | Polonia | 1 | 2010 |
| 388 | `C-1365` | `C-1365.png` | Zagłębie Sosnowiec | Polonia | 2 | 1978 |
| 389 | `C-1306` | `C-1306.png` | ŁKS | Polonia | 5 | 1974 1978 1982 |
| 390 | `C-985` | `C-985.png` | Benfica | Portogallo | 1 | 2002 |
| 391 | `C-870` | `C-870.png` | Sporting Braga | Portogallo | 1 | 2006 |
| 392 | `C-1165` | `C-1165.png` | Vitória Setúbal | Portogallo | 2 | 1966 1994 |
| 393 | `C-783` | `C-783.png` | Al Sadd | Qatar | 2 | 2002 2006 |
| 394 | `C-462` | `C-462.png` | Al-Ahli Doha | Qatar | 2 | 2022 |
| 395 | `C-848` | `C-848.png` | Al-Wakra | Qatar | 1 | 2006 |
| 396 | `C-1698` | `C-1698.png` | AMEF Arad | Romania | 2 | 1938 |
| 397 | `C-1860` | `C-1860.png` | Banatul Timișoara | Romania | 1 | 1930 |
| 398 | `C-1696` | `C-1696.png` | CAO Oradea | Romania | 4 | 1934 1938 |
| 399 | `C-1855` | `C-1855.png` | Chinezul Timișoara | Romania | 2 | 1930 |
| 400 | `C-1697` | `C-1697.png` | Crisana Oradea | Romania | 3 | 1934 1938 |
| 401 | `C-612` | `C-612.png` | Dinamo Bucarest | Romania | 21 | 1970 1990 1994 2006 2018 |
| 402 | `C-1857` | `C-1857.png` | Dragoș Vodă Cernăuți | Romania | 1 | 1930 |
| 403 | `C-1853` | `C-1853.png` | Gloria Arad | Romania | 1 | 1930 |
| 404 | `C-1796` | `C-1796.png` | ILSA Timisoara | Romania | 1 | 1934 |
| 405 | `C-766` | `C-766.png` | Int. Curtea de Argeș | Romania | 1 | 2010 |
| 406 | `C-1694` | `C-1694.png` | Juventus Bucuresti | Romania | 2 | 1938 |
| 407 | `C-1865` | `C-1865.png` | Maccabi București | Romania | 1 | 1930 |
| 408 | `C-1863` | `C-1863.png` | Olympia București | Romania | 2 | 1930 |
| 409 | `C-1110` | `C-1110.png` | Progresul Bucarest | Romania | 2 | 1998 |
| 410 | `C-1797` | `C-1797.png` | RGMT Timisoara | Romania | 1 | 1934 |
| 411 | `C-754` | `C-754.png` | Timișoara | Romania | 1 | 2010 |
| 412 | `C-1856` | `C-1856.png` | UDR Reșița | Romania | 2 | 1930 |
| 413 | `C-758` | `C-758.png` | Vaslui | Romania | 1 | 2010 |
| 414 | `C-1692` | `C-1692.png` | Venus Bucurest | Romania | 7 | 1930 1934 1938 |
| 415 | `C-1695` | `C-1695.png` | Victoria Cluj | Romania | 1 | 1938 |
| 416 | `C-543` | `C-543.png` | Achmat Groznyj | Russia | 3 | 2014 2018 |
| 417 | `C-708` | `C-708.png` | Alanija Vladikavkaz | Russia | 1 | 2010 |
| 418 | `C-696` | `C-696.png` | Anži | Russia | 1 | 2014 |
| 419 | `C-932` | `C-932.png` | Kryl'ja Sovetov Samara | Russia | 1 | 2006 |
| 420 | `C-1059` | `C-1059.png` | Uralan Elista | Russia | 2 | 2002 |
| 421 | `C-943` | `C-943.png` | Olimpia Lubiana | Slovenia | 1 | 2002 |
| 422 | `C-521` | `C-521.png` | Almería | Spagna | 3 | 2010 2014 2022 |
| 423 | `C-912` | `C-912.png` | Ciudad de Murcia | Spagna | 1 | 2006 |
| 424 | `C-1156` | `C-1156.png` | CP Mérida | Spagna | 1 | 1994 |
| 425 | `C-565` | `C-565.png` | Deportivo La Coruña | Spagna | 17 | 1994 1998 2002 2010 2018 |
| 426 | `C-801` | `C-801.png` | Deportivo La Coruña | Spagna | 4 | 1934 1950 2006 |
| 427 | `C-1327` | `C-1327.png` | Hércules | Spagna | 1 | 1982 |
| 428 | `C-419` | `C-419.png` | Racing Santander | Spagna | 6 | 1994 2002 2010 2026 |
| 429 | `C-976` | `C-976.png` | Real Sociedad | Spagna | 6 | 1934 1982 1998 2002 |
| 430 | `C-782` | `C-782.png` | Recreativo Huelva | Spagna | 1 | 2006 |
| 431 | `C-773` | `C-773.png` | Xerez | Spagna | 1 | 2010 |
| 432 | `C-1192` | `C-1192.png` | Albany Capitals | Stati Uniti | 3 | 1990 |
| 433 | `C-1196` | `C-1196.png` | Baltimore Blast | Stati Uniti | 1 | 1990 |
| 434 | `C-1807` | `C-1807.png` | Baltimore Canton | Stati Uniti | 1 | 1934 |
| 435 | `C-1814` | `C-1814.png` | Brooklyn Celtics | Stati Uniti | 1 | 1934 |
| 436 | `C-1650` | `C-1650.png` | Brooklyn Hispano | Stati Uniti | 1 | 1950 |
| 437 | `C-1652` | `C-1652.png` | Chicago Eagles | Stati Uniti | 1 | 1950 |
| 438 | `C-1648` | `C-1648.png` | Chicago Slovaks | Stati Uniti | 1 | 1950 |
| 439 | `C-1194` | `C-1194.png` | Chicago Sting | Stati Uniti | 2 | 1986 1990 |
| 440 | `C-1645` | `C-1645.png` | Chicago Vikings | Stati Uniti | 1 | 1950 |
| 441 | `C-1813` | `C-1813.png` | Chicago Wonderbolts | Stati Uniti | 1 | 1934 |
| 442 | `C-1811` | `C-1811.png` | Cleveland Slavia | Stati Uniti | 2 | 1930 1934 |
| 443 | `C-1883` | `C-1883.png` | Detroit Holley Carburetor | Stati Uniti | 1 | 1930 |
| 444 | `C-1881` | `C-1881.png` | Fall River FC | Stati Uniti | 2 | 1930 |
| 445 | `C-1651` | `C-1651.png` | Fall River Ponta Delgada S.C. | Stati Uniti | 2 | 1950 |
| 446 | `C-1129` | `C-1129.png` | Ft. Lauderdale Strikers | Stati Uniti | 3 | 1982 1990 1994 |
| 447 | `C-883` | `C-883.png` | Kansas City Wizards | Stati Uniti | 4 | 2002 2006 |
| 448 | `C-532` | `C-532.png` | LA Galaxy | Stati Uniti | 7 | 1998 2010 2014 2018 2022 |
| 449 | `C-1200` | `C-1200.png` | Los Angeles Heat | Stati Uniti | 1 | 1990 |
| 450 | `C-1000` | `C-1000.png` | MetroStars | Stati Uniti | 1 | 2002 |
| 451 | `C-1105` | `C-1105.png` | Miami Fusion | Stati Uniti | 1 | 1998 |
| 452 | `C-1191` | `C-1191.png` | Milwaukee Wave | Stati Uniti | 1 | 1990 |
| 453 | `C-1253` | `C-1253.png` | Minnesota Strikers | Stati Uniti | 1 | 1986 |
| 454 | `C-466` | `C-466.png` | N.Y. Red Bulls | Stati Uniti | 9 | 1998 2010 2014 2018 2022 |
| 455 | `C-1879` | `C-1879.png` | New Bedford Whalers | Stati Uniti | 1 | 1930 |
| 456 | `C-1816` | `C-1816.png` | New York Americans | Stati Uniti | 1 | 1934 |
| 457 | `C-1647` | `C-1647.png` | New York Brookhattan | Stati Uniti | 1 | 1950 |
| 458 | `C-1877` | `C-1877.png` | New York Giants | Stati Uniti | 3 | 1930 |
| 459 | `C-1878` | `C-1878.png` | New York Nationals | Stati Uniti | 3 | 1930 |
| 460 | `C-1808` | `C-1808.png` | Pawtucket Rangers | Stati Uniti | 4 | 1934 |
| 461 | `C-1880` | `C-1880.png` | Philadelphia Cricket Club | Stati Uniti | 1 | 1930 |
| 462 | `C-1810` | `C-1810.png` | Philadelphia German-Americans | Stati Uniti | 5 | 1934 |
| 463 | `C-1644` | `C-1644.png` | Philadelphia Nationals | Stati Uniti | 2 | 1950 |
| 464 | `C-1809` | `C-1809.png` | Pittsburgh Curry Silver Tops | Stati Uniti | 1 | 1934 |
| 465 | `C-1646` | `C-1646.png` | Pittsburgh Harmarville S.C. | Stati Uniti | 2 | 1950 |
| 466 | `C-1876` | `C-1876.png` | Providence FC | Stati Uniti | 2 | 1930 |
| 467 | `C-1190` | `C-1190.png` | S.F. Bay Blackhawks | Stati Uniti | 2 | 1990 |
| 468 | `C-1197` | `C-1197.png` | San Diego Nomads | Stati Uniti | 1 | 1990 |
| 469 | `C-1257` | `C-1257.png` | San Diego Sockers | Stati Uniti | 1 | 1986 |
| 470 | `C-1882` | `C-1882.png` | St. Louis Ben Millers | Stati Uniti | 2 | 1930 |
| 471 | `C-1649` | `C-1649.png` | St. Louis McMahon | Stati Uniti | 1 | 1950 |
| 472 | `C-1643` | `C-1643.png` | St. Louis Simpkins-Ford | Stati Uniti | 5 | 1950 |
| 473 | `C-1812` | `C-1812.png` | St. Louis Stix, Baer & Fuller | Stati Uniti | 3 | 1934 |
| 474 | `C-1815` | `C-1815.png` | St. Louis Stix, Baer & Fuller | Stati Uniti | 1 | 1934 |
| 475 | `C-1260` | `C-1260.png` | Tacoma Stars | Stati Uniti | 1 | 1986 |
| 476 | `C-1102` | `C-1102.png` | Tampa Bay Mutiny | Stati Uniti | 2 | 1998 |
| 477 | `C-1344` | `C-1344.png` | Tulsa Roughnecks | Stati Uniti | 1 | 1982 |
| 478 | `C-1130` | `C-1130.png` | USAF | Stati Uniti | 13 | 1994 |
| 479 | `C-1189` | `C-1189.png` | Virginia Cavaliers | Stati Uniti | 1 | 1990 |
| 480 | `C-1199` | `C-1199.png` | Wake Forest University | Stati Uniti | 1 | 1990 |
| 481 | `C-1195` | `C-1195.png` | Washington Stars | Stati Uniti | 2 | 1990 |
| 482 | `C-1085` | `C-1085.png` | Manning Rangers | Sudafrica | 1 | 1998 |
| 483 | `C-698` | `C-698.png` | Moroka Swallows | Sudafrica | 1 | 2010 |
| 484 | `C-947` | `C-947.png` | Santos Cape Town | Sudafrica | 1 | 2002 |
| 485 | `C-1417` | `C-1417.png` | Åtvidaberg | Svezia | 2 | 1974 |
| 486 | `C-1672` | `C-1672.png` | BK Gårda | Svezia | 1 | 1938 |
| 487 | `C-1421` | `C-1421.png` | Djurgården | Svezia | 3 | 1970 1974 |
| 488 | `C-1416` | `C-1416.png` | Hammarby | Svezia | 3 | 1970 1974 |
| 489 | `C-1442` | `C-1442.png` | Helsingborg | Svezia | 1 | 1970 |
| 490 | `C-1377` | `C-1377.png` | IFK Eskilstuna | Svezia | 1 | 1978 |
| 491 | `C-1766` | `C-1766.png` | IFK Eskilstuna | Svezia | 2 | 1934 |
| 492 | `C-1769` | `C-1769.png` | IFK Grängesberg | Svezia | 1 | 1934 |
| 493 | `C-1214` | `C-1214.png` | Öster | Svezia | 7 | 1970 1974 1978 1990 |
| 494 | `C-552` | `C-552.png` | Östersund | Svezia | 1 | 2018 |
| 495 | `C-1144` | `C-1144.png` | Västra Frölunda | Svezia | 1 | 1994 |
| 496 | `C-1762` | `C-1762.png` | FC Berna | Svizzera | 2 | 1934 |
| 497 | `C-1512` | `C-1512.png` | FC Grenchen | Svizzera | 2 | 1938 1962 |
| 498 | `C-1764` | `C-1764.png` | FC La Tour-de-Peilz | Svizzera | 1 | 1934 |
| 499 | `C-1470` | `C-1470.png` | Grenchen | Svizzera | 1 | 1966 |
| 500 | `C-1514` | `C-1514.png` | Lausanne Sports | Svizzera | 16 | 1934 1938 1950 1954 1962 |
| 501 | `C-1763` | `C-1763.png` | Nordstern Basilea | Svizzera | 1 | 1934 |
| 502 | `C-1632` | `C-1632.png` | Urania Ginevra | Svizzera | 1 | 1950 |
| 503 | `C-911` | `C-911.png` | Young Fellows Juventus | Svizzera | 1 | 2006 |
| 504 | `C-915` | `C-915.png` | Etoile Filante | Togo | 1 | 2006 |
| 505 | `C-1433` | `C-1433.png` | Archibald FC | Trinidad e Tobago | 1 | 1974 |
| 506 | `C-816` | `C-816.png` | San Juan Jabloteh | Trinidad e Tobago | 3 | 2006 |
| 507 | `C-1367` | `C-1367.png` | CO Transports | Tunisia | 1 | 1978 |
| 508 | `C-1372` | `C-1372.png` | Marsa | Tunisia | 1 | 1978 |
| 509 | `C-1370` | `C-1370.png` | Sfax Railways Sports | Tunisia | 1 | 1978 |
| 510 | `C-1604` | `C-1604.png` | Adalet Istanbul | Turchia | 2 | 1954 |
| 511 | `C-236` | `C-236.png` | Iğdır | Turchia | 2 | 2026 |
| 512 | `C-556` | `C-556.png` | Malatyaspor | Turchia | 1 | 2018 |
| 513 | `C-1084` | `C-1084.png` | Vanspor AŞ | Turchia | 2 | 1998 |
| 514 | `C-929` | `C-929.png` | Charkiv | Ucraina | 1 | 2006 |
| 515 | `C-930` | `C-930.png` | Dnipro Dnipropetrovs'k | Ucraina | 4 | 2006 |
| 516 | `C-597` | `C-597.png` | Šachtar | Ucraina | 5 | 2014 2018 |
| 517 | `C-791` | `C-791.png` | Šakhtar Doneck | Ucraina | 10 | 2002 2006 |
| 518 | `C-671` | `C-671.png` | Zorja | Ucraina | 1 | 2014 |
| 519 | `C-1761` | `C-1761.png` | Bocskay | Ungheria | 5 | 1934 |
| 520 | `C-1597` | `C-1597.png` | Csepel SC | Ungheria | 1 | 1954 |
| 521 | `C-1329` | `C-1329.png` | Debrecen | Ungheria | 1 | 1982 |
| 522 | `C-1535` | `C-1535.png` | Dorogi Bányász | Ungheria | 5 | 1954 1958 1962 |
| 523 | `C-1265` | `C-1265.png` | Pécs | Ungheria | 2 | 1982 1986 |
| 524 | `C-1598` | `C-1598.png` | Rába ETO Gyor | Ungheria | 1 | 1954 |
| 525 | `C-1534` | `C-1534.png` | Salgótarjáni BTC | Ungheria | 4 | 1954 1958 1962 |
| 526 | `C-1668` | `C-1668.png` | Szeged FC | Ungheria | 1 | 1938 |
| 527 | `C-1269` | `C-1269.png` | Zalaegerszeg | Ungheria | 1 | 1986 |
| 528 | `C-1362` | `C-1362.png` | Ararat Yerevan | Unione Sovietica | 1 | 1982 |
| 529 | `C-1436` | `C-1436.png` | Čornomorec' | Unione Sovietica | 1 | 1970 |
| 530 | `C-1478` | `C-1478.png` | Neftçi Baku | Unione Sovietica | 2 | 1966 |
| 531 | `C-1487` | `C-1487.png` | C.A. Cerro | Uruguay | 4 | 1950 1962 |
| 532 | `C-1659` | `C-1659.png` | Central Fútbol Club | Uruguay | 2 | 1950 |
| 533 | `C-1488` | `C-1488.png` | Danubio F.C. | Uruguay | 5 | 1950 1954 1962 |
| 534 | `C-531` | `C-531.png` | Nacional | Uruguay | 5 | 2010 2022 |
| 535 | `C-415` | `C-415.png` | AGMK Olmaliq | Uzbekistan | 1 | 2026 |
| 536 | `C-1412` | `C-1412.png` | AS Bilima | Zaire | 2 | 1974 |
| 537 | `C-1413` | `C-1413.png` | CS Imana | Zaire | 4 | 1974 |
| 538 | `C-1414` | `C-1414.png` | Nyiki Lubumbashi | Zaire | 1 | 1974 |

## Top 40 per impatto (stessa lista, ordinata per numero di convocati)

| club_id | File | Club | Nazione | Conv. |
|---|---|---|---|---|
| `C-865` | `C-865.png` | Chivas Guadalajara | Messico | 27 |
| `C-656` | `C-656.png` | Libertad | Paraguay | 23 |
| `C-612` | `C-612.png` | Dinamo Bucarest | Romania | 21 |
| `C-487` | `C-487.png` | Hertha Berlino | Germania | 19 |
| `C-565` | `C-565.png` | Deportivo La Coruña | Spagna | 17 |
| `C-914` | `C-914.png` | Al-Hilal | Arabia Saudita | 16 |
| `C-1605` | `C-1605.png` | Seoul Army Club | Corea del Sud | 16 |
| `C-1514` | `C-1514.png` | Lausanne Sports | Svizzera | 16 |
| `C-726` | `C-726.png` | Wisła Cracovia | Polonia | 14 |
| `C-1130` | `C-1130.png` | USAF | Stati Uniti | 13 |
| `C-569` | `C-569.png` | Universitario | Perù | 12 |
| `C-1250` | `C-1250.png` | Guaraní | Paraguay | 11 |
| `C-1244` | `C-1244.png` | Beerschot VAC | Belgio | 10 |
| `C-660` | `C-660.png` | El Nacional | Ecuador | 10 |
| `C-791` | `C-791.png` | Šakhtar Doneck | Ucraina | 10 |
| `C-1589` | `C-1589.png` | Wacker Vienna | Austria | 9 |
| `C-1480` | `C-1480.png` | Vojvodina Novi Sad | Jugoslavia | 9 |
| `C-466` | `C-466.png` | N.Y. Red Bulls | Stati Uniti | 9 |
| `C-1661` | `C-1661.png` | Litoral | Bolivia | 8 |
| `C-568` | `C-568.png` | Atlético Junior | Colombia | 8 |
| `C-456` | `C-456.png` | QPR | Inghilterra | 8 |
| `C-1246` | `C-1246.png` | Al-Rasheed | Iraq | 8 |
| `C-518` | `C-518.png` | Wydad Casablanca | Marocco | 8 |
| `C-1723` | `C-1723.png` | Daring Club de Bruxelles Societe Royale | Belgio | 7 |
| `C-1541` | `C-1541.png` | CDNA Sofija | Bulgaria | 7 |
| `C-760` | `C-760.png` | Amnokgang | Corea del Nord | 7 |
| `C-894` | `C-894.png` | Seongnam Ilhwa Chunma | Corea del Sud | 7 |
| `C-1692` | `C-1692.png` | Venus Bucurest | Romania | 7 |
| `C-532` | `C-532.png` | LA Galaxy | Stati Uniti | 7 |
| `C-1214` | `C-1214.png` | Öster | Svezia | 7 |
| `C-907` | `C-907.png` | Admira Wacker Mödling | Austria | 6 |
| `C-1451` | `C-1451.png` | Portuguesa | Brasile | 6 |
| `C-1206` | `C-1206.png` | Union Douala | Camerun | 6 |
| `C-509` | `C-509.png` | CF Montréal | Canada | 6 |
| `C-1800` | `C-1800.png` | Zamalek Mokhtalat | Egitto | 6 |
| `C-1689` | `C-1689.png` | Sète | Francia | 6 |
| `C-1177` | `C-1177.png` | Kawkab Marrakech | Marocco | 6 |
| `C-419` | `C-419.png` | Racing Santander | Spagna | 6 |
| `C-976` | `C-976.png` | Real Sociedad | Spagna | 6 |
| `C-1786` | `C-1786.png` | Club Atlético Estudiantil Porteño | Argentina | 5 |
