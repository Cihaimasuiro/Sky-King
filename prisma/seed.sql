INSERT IGNORE INTO
  `Airport` (`name`, `city`, `country`, `code`)
VALUES
  ('Soekarno-Hatta International Airport', 'Jakarta', 'Indonesia', 'CGK'),
  ('Ngurah Rai International Airport', 'Denpasar', 'Indonesia', 'DPS'),
  ('Changi Airport', 'Singapore', 'Singapore', 'SIN'),
  ('Haneda Airport', 'Tokyo', 'Japan', 'HND'),
  ('Incheon International Airport', 'Seoul', 'South Korea', 'ICN'),
  ('Sydney Kingsford Smith Airport', 'Sydney', 'Australia', 'SYD');

INSERT INTO
  `Flight` (
    `departureAirportId`,
    `arrivalAirportId`,
    `departureTime`,
    `arrivalTime`,
    `price`
  )
SELECT
  departure.id,
  arrival.id,
  '2026-06-04 08:45:00.000',
  '2026-06-04 11:35:00.000',
  185
FROM
  `Airport` departure,
  `Airport` arrival
WHERE
  departure.code = 'CGK'
  AND arrival.code = 'DPS'
  AND NOT EXISTS (
    SELECT
      1
    FROM
      `Flight`
    WHERE
      `departureAirportId` = departure.id
      AND `arrivalAirportId` = arrival.id
      AND `departureTime` = '2026-06-04 08:45:00.000'
  );

INSERT INTO
  `Flight` (
    `departureAirportId`,
    `arrivalAirportId`,
    `departureTime`,
    `arrivalTime`,
    `price`
  )
SELECT
  departure.id,
  arrival.id,
  '2026-06-09 13:20:00.000',
  '2026-06-09 16:05:00.000',
  240
FROM
  `Airport` departure,
  `Airport` arrival
WHERE
  departure.code = 'CGK'
  AND arrival.code = 'SIN'
  AND NOT EXISTS (
    SELECT
      1
    FROM
      `Flight`
    WHERE
      `departureAirportId` = departure.id
      AND `arrivalAirportId` = arrival.id
      AND `departureTime` = '2026-06-09 13:20:00.000'
  );

INSERT INTO
  `Flight` (
    `departureAirportId`,
    `arrivalAirportId`,
    `departureTime`,
    `arrivalTime`,
    `price`
  )
SELECT
  departure.id,
  arrival.id,
  '2026-06-14 23:10:00.000',
  '2026-06-15 08:25:00.000',
  615
FROM
  `Airport` departure,
  `Airport` arrival
WHERE
  departure.code = 'SIN'
  AND arrival.code = 'HND'
  AND NOT EXISTS (
    SELECT
      1
    FROM
      `Flight`
    WHERE
      `departureAirportId` = departure.id
      AND `arrivalAirportId` = arrival.id
      AND `departureTime` = '2026-06-14 23:10:00.000'
  );

INSERT INTO
  `Flight` (
    `departureAirportId`,
    `arrivalAirportId`,
    `departureTime`,
    `arrivalTime`,
    `price`
  )
SELECT
  departure.id,
  arrival.id,
  '2026-06-18 09:15:00.000',
  '2026-06-18 16:50:00.000',
  540
FROM
  `Airport` departure,
  `Airport` arrival
WHERE
  departure.code = 'DPS'
  AND arrival.code = 'SYD'
  AND NOT EXISTS (
    SELECT
      1
    FROM
      `Flight`
    WHERE
      `departureAirportId` = departure.id
      AND `arrivalAirportId` = arrival.id
      AND `departureTime` = '2026-06-18 09:15:00.000'
  );

INSERT INTO
  `Flight` (
    `departureAirportId`,
    `arrivalAirportId`,
    `departureTime`,
    `arrivalTime`,
    `price`
  )
SELECT
  departure.id,
  arrival.id,
  '2026-06-22 01:30:00.000',
  '2026-06-22 09:45:00.000',
  490
FROM
  `Airport` departure,
  `Airport` arrival
WHERE
  departure.code = 'ICN'
  AND arrival.code = 'CGK'
  AND NOT EXISTS (
    SELECT
      1
    FROM
      `Flight`
    WHERE
      `departureAirportId` = departure.id
      AND `arrivalAirportId` = arrival.id
      AND `departureTime` = '2026-06-22 01:30:00.000'
  );
