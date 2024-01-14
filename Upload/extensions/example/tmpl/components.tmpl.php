<div class="box">
	<div class="box-body">
		<div id="app">


            <div class="component">
                <h3>Modal</h3>
                <h4>Vorschau:</h4>

                <div class="" >
                    <button class="si-btn">Button</button>
                    <button class="si-btn"><i class="fa fa-child"></i> Button & Icon</button>
                    <br>
                    <button class="si-btn si-btn-active">si-btn-active</button>
                    <br>
                    <button class="si-btn si-btn-light">si-btn-light</button>
                    <br>
                    <button class="si-btn si-btn-green">si-btn-green</button>
                    <button class="si-btn si-btn-red">si-btn-red</button>
                </div>

                <h4>Code:</h4>
                <textarea readonly><button class="si-btn">Button</button>
<button class="si-btn"><i class="fa fa-child"></i> Button & Icon</button>
<button class="si-btn si-btn-active">si-btn-active</button>
<button class="si-btn si-btn-light">si-btn-light</button>
<button class="si-btn si-btn-green">si-btn-green</button>
<button class="si-btn si-btn-red">si-btn-red</button></textarea>
            </div>




            <div class="component">
                <h3>User</h3>
                <h4>Vorschau:</h4>

                <h3>Normal:</h3>
                <div class="si-user isPupil">
                    <div class="avatar">
                        <img src="cssjs/images/userimages/default.png" alt="" title=""/>
                    </div>
                    <div class="info">
                        <div class="top">Vorname</div>
                        <div class="bottom">
                            <span class="name">Nachname</span>
                            <span class="klasse">Klasse</span>
                        </div>
                    </div>
                </div>

                <h3>Teacher:</h3>
                <div class="si-user isTeacher">
                    <div class="avatar">
                        <img src="cssjs/images/userimages/default.png" alt="" title=""/>
                    </div>
                    <div class="info">
                        <div class="top">Vorname</div>
                        <div class="bottom">
                            <span class="name">Nachname</span>
                        </div>
                    </div>
                </div>

                <h3>isNone:</h3>
                <div class="si-user isNone">
                    <div class="avatar">
                        <img src="cssjs/images/userimages/default.png" alt="" title=""/>
                    </div>
                    <div class="info">
                        <div class="top">Vorname</div>
                        <div class="bottom">
                            <span class="name">Nachname</span>
                        </div>
                    </div>
                </div>

                <h3>isEltern:</h3>
                <div class="si-user isEltern">
                    <div class="avatar">
                        <img src="cssjs/images/userimages/default.png" alt="" title=""/>
                    </div>
                    <div class="info">
                        <div class="top">Vorname</div>
                        <div class="bottom">
                            <span class="name">Nachname</span>
                        </div>
                    </div>
                </div>

                <h3>si-user-line:</h3>
                <div class="si-user si-user-line">
                    Benutzername
                </div>

                <h3>Infobox:</h3>
                <h4>.si-user--infoBox</h4>
                <div class="si-user--infoBox">
                    <div class="avatar">
                        <img src="cssjs/images/userimages/default.png" alt="" title=""/>
                    </div>

                    <div class="vorname">Vorname</div>
                    <div class="nachname">Nachname</div>

                    <div v-if="data.type=='isTeacher'" class="type isTeacher">- Lehrer -</div>
                    <div v-if="data.type=='isPupil'" class="type isPupil">
                        <div class="klasse"><label>Klasse:</label> 9b</div>
                    </div>

                    <div class="toolbar">
                        <a v-if="data.id" :href="'index.php?page=MessageCompose&recipient=U:'+data.id"  class="si-btn"><i class="fa fa-envelope"></i> Anschreiben</a>
                    </div>
                </div>



                <h4>Code:</h4>
                <textarea readonly><div class="si-user isPupil">
    <div class="avatar">
        <img src="cssjs/images/userimages/default.png" alt="" title=""/>
    </div>
    <div class="info">
        <div class="top">Vorname</div>
        <div class="bottom">
            <span class="name">Nachname</span>
            <span class="klasse">Klasse</span>
        </div>
    </div>
</div></textarea>
            </div>




            <div class="component">
                <h3>Tabelle</h3>
                <h4>Vorschau:</h4>

                <table class="si-table">
                    <thead>
                        <tr>
                            <td>Spalte 1</td>
                            <td>Spalte 2</td>
                            <td>Spalte 3</td>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>1</td>
                            <td>a</td>
                            <td>x</td>
                        </tr>
                        <tr>
                            <td>2</td>
                            <td>b</td>
                            <td>y</td>
                        </tr>
                        <tr>
                            <td>3</td>
                            <td>c</td>
                            <td>z</td>
                        </tr>
                    </tbody>
                </table>

                <h4>Nested:</h4>
                <table class="si-table ">
                    <thead>
                    <tr>
                        <td>Spalte 1</td>
                        <td>Spalte 2</td>
                    </tr>
                    </thead>
                    <tbody>
                    <tr>
                        <td>1</td>
                        <td>
                            <table class="si-table si-table-style-firstLeft">
                                <thead>
                                <tr>
                                    <td>Spalte 1</td>
                                    <td>Spalte 2</td>
                                </tr>
                                </thead>
                                <tbody>
                                <tr>
                                    <td>2</td>
                                    <td>
                                        <table class="si-table">
                                            <thead>
                                            <tr>
                                                <td>Spalte 1</td>
                                                <td>Spalte 2</td>
                                                <td>Spalte 3</td>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            <tr>
                                                <td>3</td>
                                                <td>a</td>
                                                <td>b</td>
                                            </tr>
                                            </tbody>
                                        </table>
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                        </td>
                    </tr>
                    </tbody>
                </table>

                <h3>Mehr:</h3>
                <ul>
                    <li>
                        <label>si-table-style-firstLeft</label>
                        - Erste Spalte linksbündig
                    </li>
                    <li>
                        <label>si-table-style-alltLeft</label>
                        - Alle Spalten linksbündig
                    </li>
                </ul>
                <h4>Code:</h4>
                <textarea readonly><table class="si-table">
    <thead>
        <tr>
            <td>Spalte 1</td>
            <td>Spalte 2</td>
            <td>Spalte 3</td>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>1</td>
            <td>a</td>
            <td>x</td>
        </tr>
        <tr>
            <td>2</td>
            <td>b</td>
            <td>y</td>
        </tr>
        <tr>
            <td>3</td>
            <td>c</td>
            <td>z</td>
        </tr>
    </tbody>
</table></textarea>
            </div>



            <div class="component">
                <h3>Modal</h3>
                <h4>Vorschau:</h4>

                <div class="si-modal" >
                    <div class="si-modal-box" >
                        <button class="si-modal-btn-close" ></button>
                        <div class="si-modal-content">
                            <h3 class="box-title">Title</h3>
                            Hier der Inhalt
                        </div>
                    </div>
                </div>

                <h4>Code:</h4>
                <textarea readonly><div class="si-modal" >
    <div class="si-modal-box" >
        <button class="si-modal-btn-close" ></button>
        <div class="si-modal-content">
            Hier der Inhalt
        </div>
    </div>
</div></textarea>
            </div>


            <div class="component">
                <h3>Formular</h3>
                <h4>Vorschau:</h4>

                <div class="si-form">
                    <ul>
                        <li>
                            <label>Auswahlfeld</label>
                            <label class="small">Kleiner Text</label>
                            <input type="text" />
                        </li>
                        <li>
                            <label>Auswahlfeld</label>
                            <select>
                                <option>Hallo</option>
                                <option>Welt</option>
                            </select>
                        </li>
                    </ul>
                </div>

                <h4>Code:</h4>
                <textarea readonly><div class="si-form">
    <ul>
        <li>
            <label>Auswahlfeld</label>
            <label class="small">Kleiner Text</label>
            <input type="text" />
        </li>
        <li>
            <label>Auswahlfeld</label>
            <select>
                <option>Hallo</option>
                <option>Welt</option>
            </select>
        </li>
    </ul>
</div></textarea>
            </div>

            <div class="component">
                <h3>Hinweisbox</h3>
                <h4>Vorschau:</h4>
                <div class="si-hinweis">Hier der Hinweistext</div>
                <h4>Code:</h4>
                <textarea readonly><div class="si-hinweis">Hier der Hinweistext</div></textarea>
            </div>

            <div class="component">
                <h3>Hinweismeldung Succeed</h3>
                <h4>Vorschau:</h4>
                <div class="si-succeed">
                    <div class="msg">Hier der Inhalt</div>
                </div>
                <h4>Code:</h4>
<textarea readonly><div class="si-succeed">
    <div class="msg">Hier der Inhalt</div>
</div></textarea>
            </div>

            <div class="component">
                <h3>Hinweismeldung Error</h3>
                <h4>Vorschau:</h4>
                <div class="si-error">
                    <div class="head">Error:</div>
                    <div class="msg">Fehlernachricht</div>
                </div>
                <h4>Code:</h4>
                <textarea readonly><div className="si-error">
    <div className="head">Error:</div>
    <div className="msg">Fehlernachricht</div>
</div></textarea>
            </div>

        </div>
	</div>
</div>

<style>
    .component {
        padding: 1rem;
        border: 1px solid #ccc;
    }

    .component .si-succeed,
    .component .si-error{
        position: relative;
    }

    .component .si-modal {
        position: relative;
        width: auto;
        height: auto;
    }
    .si-modal .si-modal-box {
        top: 12rem;
    }

    .component .si-user--infoBox {
        padding: 1rem;
        border: 1px solid #ccc;
    }
</style>