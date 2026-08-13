<?php
/**
 * backend/config/semente.php — Dados iniciais (seed)
 * Espelha o conteúdo de database/bd_mundo.sql para o modo demonstração.
 * Troque/edite à vontade: dados reais valorizam o trabalho.
 */

function semear(PDO $pdo): void
{
    $continentes = [
        [1, 'América do Sul',   434000000,  17840000, 12],
        [2, 'América do Norte', 592000000,  24709000, 23],
        [3, 'Europa',           746000000,  10180000, 44],
        [4, 'Ásia',            4750000000, 44579000, 49],
        [5, 'África',          1460000000, 30370000, 54],
        [6, 'Oceania',           45000000,   8510000, 14],
    ];

    $governantes = [
        [1,  'Luiz Inácio Lula da Silva', 'PT',                          '1945-10-27', 80, '2023-01-01', '2026-12-31'],
        [2,  'Javier Milei',              'La Libertad Avanza',          '1970-10-22', 55, '2023-12-10', '2027-12-10'],
        [3,  'Marcelo Rebelo de Sousa',   'PSD',                         '1948-12-12', 77, '2021-03-09', '2026-03-09'],
        [4,  'Emmanuel Macron',           'Renaissance',                 '1977-12-21', 48, '2022-05-14', '2027-05-14'],
        [5,  'Friedrich Merz',            'CDU',                         '1955-11-11', 70, '2025-05-06', '2029-05-06'],
        [6,  'Keir Starmer',              'Partido Trabalhista',         '1962-09-02', 63, '2024-07-05', '2029-07-05'],
        [7,  'Donald Trump',              'Partido Republicano',         '1946-06-14', 80, '2025-01-20', '2029-01-20'],
        [8,  'Mark Carney',               'Partido Liberal',             '1965-03-16', 61, '2025-03-14', '2029-03-14'],
        [9,  'Shigeru Ishiba',            'PLD',                         '1957-02-04', 69, '2024-10-01', '2028-10-01'],
        [10, 'Xi Jinping',                'Partido Comunista da China',  '1953-06-15', 73, '2023-03-10', '2028-03-10'],
        [11, 'Narendra Modi',             'BJP',                         '1950-09-17', 75, '2024-06-09', '2029-06-09'],
        [12, 'Cyril Ramaphosa',           'ANC',                         '1952-11-17', 73, '2024-06-19', '2029-06-19'],
        [13, 'Ricardo Nunes',             'MDB',                         '1967-11-13', 58, '2025-01-01', '2028-12-31'],
        [14, 'Eduardo Paes',              'PSD',                         '1969-11-14', 56, '2025-01-01', '2028-12-31'],
        [15, 'Ibaneis Rocha',             'MDB',                         '1971-07-10', 55, '2023-01-01', '2026-12-31'],
        [16, 'Anne Hidalgo',              'Partido Socialista',          '1959-06-19', 67, '2020-06-28', '2026-06-28'],
        [17, 'Yuriko Koike',              'Independente',                '1952-07-15', 74, '2024-08-01', '2028-08-01'],
        [18, 'Carlos Moedas',             'PSD',                         '1970-08-10', 56, '2021-10-18', '2029-10-18'],
    ];

    $paises = [
        [1,  1, 1,  'Brasil',          203100000,  8510416, 'Português',            'Tropical',          'República Presidencialista', 'Real (BRL)'],
        [2,  1, 2,  'Argentina',        46600000,  2780400, 'Espanhol',             'Temperado',         'República Presidencialista', 'Peso argentino (ARS)'],
        [3,  3, 3,  'Portugal',         10600000,    92226, 'Português',            'Mediterrânico',     'República Parlamentar',      'Euro (EUR)'],
        [4,  3, 4,  'França',           68400000,   551695, 'Francês',              'Temperado Oceânico','República Semipresidencialista', 'Euro (EUR)'],
        [5,  3, 5,  'Alemanha',         84700000,   357588, 'Alemão',               'Temperado',         'República Parlamentar',      'Euro (EUR)'],
        [6,  3, 6,  'Reino Unido',      68300000,   243610, 'Inglês',               'Temperado Oceânico','Monarquia Parlamentar',      'Libra esterlina (GBP)'],
        [7,  2, 7,  'Estados Unidos',  340100000,  9833517, 'Inglês',               'Climas diversos',   'República Presidencialista', 'Dólar (USD)'],
        [8,  2, 8,  'Canadá',           41500000,  9984670, 'Inglês e Francês',     'Subártico',         'Monarquia Parlamentar',      'Dólar canadense (CAD)'],
        [9,  4, 9,  'Japão',           123900000,   377975, 'Japonês',              'Temperado',         'Monarquia Constitucional',   'Iene (JPY)'],
        [10, 4, 10, 'China',          1409000000,  9596961, 'Mandarim',             'Climas diversos',   'República Popular',          'Yuan (CNY)'],
        [11, 4, 11, 'Índia',          1428000000,  3287263, 'Hindi e Inglês',       'Tropical',          'República Parlamentar',      'Rúpia (INR)'],
        [12, 5, 12, 'África do Sul',    63200000,  1221037, '11 idiomas oficiais',  'Semiárido',         'República Parlamentar',      'Rand (ZAR)'],
    ];

    $cidades = [
        [1,  1,  13, 'São Paulo',      11451245, 1521,  'Tropical de altitude',     '1554-01-25'],
        [2,  1,  14, 'Rio de Janeiro',  6211223, 1200,  'Tropical Atlântico',       '1565-03-01'],
        [3,  1,  15, 'Brasília',        2817381, 5760,  'Tropical de altitude',     '1960-04-21'],
        [4,  2,  null, 'Buenos Aires',  3121707,  203,  'Temperado pampeano',       '1536-02-02'],
        [5,  3,  18, 'Lisboa',           545796,  100,  'Mediterrânico',            null],
        [6,  3,  null, 'Porto',          231800,   41,  'Oceânico',                 null],
        [7,  4,  16, 'Paris',           2103000,  105,  'Oceânico',                 null],
        [8,  5,  null, 'Berlim',        3755251,  892,  'Temperado continental',    null],
        [9,  6,  null, 'Londres',       8866000, 1572,  'Oceânico',                 null],
        [10, 9,  17, 'Tóquio',         13960000, 2191,  'Temperado úmido',          null],
        [11, 10, null, 'Pequim',       21890000, 16410, 'Continental',              null],
        [12, 10, null, 'Xangai',       24870000, 6340,  'Subtropical úmido',        null],
        [13, 11, null, 'Nova Délhi',   32900000, 1484,  'Semiárido',                '1931-02-13'],
        [14, 12, null, 'Joanesburgo',   5635000, 1645,  'Subtropical de altitude',  '1886-10-04'],
        [15, 7,  null, 'Nova York',     8336000,  783,  'Continental úmido',        null],
        [16, 8,  null, 'Toronto',       2930000,  630,  'Continental úmido',        null],
    ];

    $st = $pdo->prepare('INSERT INTO continentes (id_continente, nome, populacao, area_km2, total_paises) VALUES (?,?,?,?,?)');
    foreach ($continentes as $l) { $st->execute($l); }

    $st = $pdo->prepare('INSERT INTO governantes (id_governante, nome, partido_politico, data_nascimento, idade, inicio_mandato, fim_mandato) VALUES (?,?,?,?,?,?,?)');
    foreach ($governantes as $l) { $st->execute($l); }

    $st = $pdo->prepare('INSERT INTO paises (id_pais, id_continente, id_governante, nome, populacao, area_km2, idioma, clima, regime_politico, moeda) VALUES (?,?,?,?,?,?,?,?,?,?)');
    foreach ($paises as $l) { $st->execute($l); }

    $st = $pdo->prepare('INSERT INTO cidades (id_cidade, id_pais, id_governante, nome, populacao, area_km2, clima, data_fundacao) VALUES (?,?,?,?,?,?,?,?)');
    foreach ($cidades as $l) { $st->execute($l); }
}
