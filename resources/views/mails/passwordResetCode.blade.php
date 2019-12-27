<!DOCTYPE html>
<div class="container_individual">
    <h2>Passwort zurücksetzen</h2>
    <p>
    Sehr geehrter Herr {{$user->lastname}},<br>
    Mit folgendem Link können Sie ihr Passwort zurücksetzen.
    </p>
    <p>
        <a href="http://localhost:3000/account/passwordresetcodevalidation?userID={{$user->id}}&password_reset_code={{$password_reset_code}}">Passwort zurücksetzen</a>
    </p>

    Dieser Link gilt aus Sicherheitsgründen nur für 24 Stunden.
</div>

<style>
.container_individual{
    width:80%;
    margin:auto;
    background-color: whitesmoke;
    padding:10px;
}

</style>