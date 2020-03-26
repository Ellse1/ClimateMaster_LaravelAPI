<!DOCTYPE html>
    <div class="background-container">
        <div class="container_individual">

            <span style="font-size:30px;"><span style="color:green;">Climate</span>Master</span>
            <h5 style="text-align: right;">Klimaschutz jetzt</h5>
            <p class="headline">Passwortreset bei <span style="color:green">Climate</span>Master</p>
            <p>
            Lieber {{$user->firstname}},<br>
            Hast du vor kurzer Zeit versucht dich bei ClimateMaster einzuloggen aber dein Passwort vergessen und willst dieses nun ändern?
            Mit folgendem Link kannst du dein Passwort zurücksetzen.
            </p>
            <p style="text-align: center;margin:auto;margin-top:20px;margin-bottom:20px;">
                <a style="text-align:center;margin:auto;border-color:green;border-radius:10px;border-style:solid;padding:10px;"  href="https://www.climate-master.com/account/passwordresetcodevalidation?userID={{$user->id}}&password_reset_code={{$password_reset_code}}">Passwort zurücksetzen</a>
            </p>

            <p style="text-align:center;">
                Falls du dein Passwort nicht zurücksetzen willst und dies auch nicht versucht hast, solltest du aus Sicherheitsgründen dein Passwort ändern.
            </p>
        </div>
    </div>
<style>
html{
    background-color: whitesmoke;
}
.background-container{
    background-color: whitesmoke;
    height:100%;
}
.container_individual{
    max-width:600px;
    margin:auto;
    margin-top:30px;
    background-color: white;
    padding-top:10px;
}
.headline{
    width:100%;
    font-size:50px;
    text-align: center;
    background-color: #30CDF5;
    padding-top:10px;
    padding-bottom: 10px;
}
</style>
