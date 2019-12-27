<!DOCTYPE html>
<div class="container_individual">
    <h2>Registrierung bei <span style="color:green">Climate</span>Master</h2>
    <p>
    Sehr geehrter Herr {{$user->lastname}},<br>
    Sie haben sich kürzlich bei Climate-master angemeldet. Wir sind sehr froh, dass es Leute 
    wie Sie gibt, die sich für Umwelt und Klimaschutz einsetzen!! 
    Für die Aktivierung ihres Kontos, folgen Sie einfach dem folgenden Link:
    </p>
    <p>
        <a href="http://localhost:3000/account/verification?userID={{$user->id}}&verificationCode={{$verificationCode}}">Verifizieren</a>
    </p>

    Vielen Dank für die Aktivierung ihres ClimateMaster Kontos.
</div>

<style>
.container_individual{
    width:80%;
    margin:auto;
    background-color: whitesmoke;
    padding:10px;
}

</style>