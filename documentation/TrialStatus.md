## BasicTrial status
### Status list :
```
CREATED <=> ARBITRE CREER MATCH, ATTENTE CONFIRMATION DE DATE PAR LES DEUX USER
DATE_ACCEPTED <=> 1 ACCEPTE LA DATE ET LE MATCH
AWAITING <=> 2 ACCEPTE LA DATE ET LE MATCH / ATTENTE DU MATCH
STARTED <=> COMMENCER
ENDED <=> FINIS
DATE_REFUSED <=> TRIAL ATTEND UNE MODIFICATION DE DATE PAR L'ARBITRE
REFUSED <=> MATCH REFUSER, AUCUNE MODIFICATION EXTERNE ATTENDUE
```
### Workflow :
Normal behavior : 
```
CREATED -> DATE_ACCEPTED -> AWAITING -> STARTED -> ENDED
```
User refuse date : 
This worklow can loop until we arrive to an AWAITING status or a REFUSED
```
CREATED -> DATE_ACCEPTED|DATE_REFUSED -> DATE_REFUSED -> CREATED -> ...
```
User refuse match :
Users can refuse match at any time. It cancels the whole workflow and doesn't require any new changes
```
CREATED -> ... -> REFUSED
```

## ChallengedTrial status
### Status list :
```
CREATED <=> USER1 CHALLENGED USER2
ACCEPTED <=> USER2 ACCEPTED
VALIDATED <=> REFEREE ACCEPTED
REFUSED <=> MATCH REFUSER, AUCUNE MODIFICATION EXTERNE ATTENDUE
```
### Workflow :
Normal behavior :
This behavior is before a normal match behavior.
```
CREATED -> ACCEPTED -> VALIDATED -> ...
```
As for normal trial, users can refuse at any time