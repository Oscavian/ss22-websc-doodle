# DB Mockup

Appointments:
- id
- Titel
- Ersteller
- Beschreibung
- Ort
- Datum
- Ablaufdatum

Termine:
- termin_id
- _appointment_id_
- datum
- beginn_zeit
- ende_zeit

user-termin:
- _user_id_
- _termin_id_

User:
- id
- namen

Kommentare:
- _user_id_
- _appointment_id_
- text

## Quickdatabasediagram model

https://app.quickdatabasediagrams.com
```
Appointment as app
----
app_id int pk autoincrement
title varchar(200)
creator varchar(200)
description varchar(500)
location varchar(200)
creation_date timestamp
expiration_date timestamp

Timeslot as ts
---
slot_id pk
app_id int fk >- app.app_id
start_datetime timestamp
end_datetime timestamp

Participant as pt
----
id int pk
username varchar(200)
slot_id fk >- ts.slot_id

Comment
----
id int pk
username varchar(200)
message varchar(500)
app_id fk >- app.app_id
```