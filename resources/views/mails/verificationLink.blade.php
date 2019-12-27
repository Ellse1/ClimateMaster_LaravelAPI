<!DOCTYPE html>
<div class="container_individual">
    <h2>Verifizierung bei <span style="color:green">Climate</span>Master</h2>
    <p>
    Sie haben einen neuen Verifizierungslink angeforder. Folgen Sie dem unten stehenden Link, um Ihr Konto zu aktivieren.
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