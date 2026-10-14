#!/usr/bin/env python3
"""Vérification HTTP sans dépendance externe, sur les comptes de démonstration."""
import html
import http.cookiejar
import re
import sys
import urllib.error
import urllib.parse
import urllib.request
import uuid
from html.parser import HTMLParser

BASE = (sys.argv[1] if len(sys.argv) > 1 else 'http://localhost:8081').rstrip('/')
CHECKS = 0

def check(condition, label):
    global CHECKS
    if not condition:
        raise AssertionError(label)
    CHECKS += 1
    print('OK - ' + label)

class Fields(HTMLParser):
    def __init__(self, text):
        super().__init__()
        self.fields = {}
        self.feed(text)
    def handle_starttag(self, tag, attrs):
        attrs = dict(attrs)
        if tag == 'input' and 'name' in attrs:
            self.fields[attrs['name']] = attrs.get('value', '')

class Client:
    def __init__(self):
        self.cookies = http.cookiejar.CookieJar()
        self.opener = urllib.request.build_opener(urllib.request.HTTPCookieProcessor(self.cookies))
    def request(self, route, data=None, **params):
        url = BASE + '/?' + urllib.parse.urlencode({'route': route, **params})
        body = urllib.parse.urlencode(data).encode() if data is not None else None
        try:
            response = self.opener.open(url, body, timeout=20)
        except urllib.error.HTTPError as error:
            response = error
        return response.status, response.read().decode(), response.headers
    def token(self, route='tasks', **params):
        status, page, _ = self.request(route, **params)
        if status != 200:
            raise AssertionError('Chargement du formulaire : ' + str(status))
        return Fields(page).fields['_token']
    def post(self, route, data=None, **params):
        token = self.token('login' if route == 'login' else 'tasks')
        return self.request(route, {'_token': token, **(data or {})}, **params)
    def login(self, email):
        return self.post('login', {'email': email, 'password': 'Demo1234!'})

def ids(page, entity):
    return set(map(int, re.findall(r'route=' + entity + r'-edit&id=(\d+)', html.unescape(page))))

alice, bob = Client(), Client()
project_id = task_id = None
marker = 'QA-' + uuid.uuid4().hex[:10]
try:
    status, page, headers = alice.request('dashboard')
    check(status == 200 and 'Bienvenue' in page, 'Accès anonyme redirigé vers la connexion')
    check('default-src' in headers.get('Content-Security-Policy', ''), 'Politique CSP présente')
    check(alice.request('login', {'email': 'x', 'password': 'x'})[0] == 419, 'POST sans jeton rejeté')
    status, page, _ = alice.post('login', {'email': 'julien@taskflow.test', 'password': 'incorrect'})
    check(status == 422 and 'incorrect' in page, 'Mauvais mot de passe rejeté')
    check(alice.login('julien@taskflow.test')[0] == 200, 'Connexion Julien')
    check(bob.login('camille@taskflow.test')[0] == 200, 'Connexion Camille')
    status, page, _ = alice.request('projects')
    original_projects = ids(page, 'project')
    check(len(original_projects) == 5, 'Cinq projets de démonstration visibles pour Julien')
    _, page, _ = alice.request('tasks')
    check(len(ids(page, 'task')) == 40, 'Quarante tâches de démonstration visibles pour Julien')
    for route in ['dashboard', 'projects', 'tasks', 'project-new', 'task-new']:
        check(alice.request(route)[0] == 200, 'Page ' + route)
    check(alice.request('project-delete', id=1)[0] == 405, 'Suppression interdite en GET')
    check(alice.request('inconnue')[0] == 404, 'Route inconnue : 404')
    check(alice.request('project-edit', id=9999999)[0] == 404, 'Projet absent : 404')
    check(alice.post('project-new', {'name': '', 'description': '', 'color': '#6366f1'})[0] == 422, 'Nom de projet requis')
    check(alice.post('project-new', {'name': 'é'*101, 'color': '#6366f1'})[0] == 422, 'Longueur Unicode validée')
    project = {'name': marker, 'description': '<script>alert(1)</script>', 'color': '#0d9488'}
    status, page, _ = alice.post('project-new', project)
    check(status == 200 and marker in page, 'Création du projet')
    project_id = (ids(page, 'project') - original_projects).pop()
    check('<script>alert(1)</script>' not in page and '&lt;script&gt;' in page, 'Contenu HTML échappé')
    project['name'] = marker + '-modifie'
    check(alice.post('project-edit', project, id=project_id)[0] == 200, 'Modification du projet')
    check(bob.request('project-edit', id=project_id)[0] == 404, 'Lecture du projet interdite à Camille')
    check(bob.post('project-delete', {'confirm': 'yes'}, id=project_id)[0] == 404, 'Suppression du projet interdite à Camille')
    task = {'title': marker + '-tache', 'description': 'Une description', 'project_id': str(project_id), 'status': 'todo', 'priority': 'high', 'due_date': '2030-02-28'}
    check(alice.post('task-new', {**task, 'due_date': '2030-02-30'})[0] == 422, 'Date impossible rejetée')
    check(alice.post('task-new', {**task, 'status': 'invalid'})[0] == 422, 'Statut invalide rejeté')
    check(bob.post('task-new', task)[0] == 422, 'Affectation à un projet appartenant à autrui rejetée')
    check(alice.post('task-new', task)[0] == 200, 'Création de la tâche')
    status, page, _ = alice.request('tasks', q=marker)
    found = ids(page, 'task')
    check(len(found) == 1, 'Recherche par titre')
    task_id = found.pop()
    check(bob.request('task-edit', id=task_id)[0] == 404, 'Lecture de la tâche interdite à Camille')
    check(bob.post('task-edit', task, id=task_id)[0] == 404, 'Modification de la tâche interdite à Camille')
    check(bob.post('task-delete', {'confirm': 'yes'}, id=task_id)[0] == 404, 'Suppression de la tâche interdite à Camille')
    check(ids(alice.request('tasks', project=project_id, status='todo')[1], 'task') == {task_id}, 'Filtres projet et statut combinés')
    check(not ids(alice.request('tasks', project=project_id, status='done')[1], 'task'), 'Filtre excluant les tâches non terminées')
    task.update(status='done', priority='low', title=marker+'-terminee', due_date='')
    check(alice.post('task-edit', task, id=task_id)[0] == 200, 'Modification statut, priorité et suppression échéance')
    check(ids(alice.request('tasks', project=project_id, status='done')[1], 'task') == {task_id}, 'Statut terminé persisté')
    check(not ids(alice.request('tasks', q="%' OR 1=1 --")[1], 'task'), 'Requête SQL malveillante traitée comme une valeur')
    check(alice.request('task-delete', {'confirm': 'yes'}, id=task_id)[0] == 419, 'CSRF sur suppression rejeté')
    check(alice.post('task-delete', {}, id=task_id)[0] == 422, 'Confirmation de suppression requise')
    check(alice.post('task-delete', {'confirm': 'yes'}, id=task_id)[0] == 200, 'Suppression de la tâche')
    check(alice.request('task-edit', id=task_id)[0] == 404, 'Tâche supprimée absente')
    # Vérifier séparément la cascade de suppression du projet.
    alice.post('task-new', {**task, 'status': 'doing'})
    cascade_id = ids(alice.request('tasks', project=project_id)[1], 'task').pop()
    check(alice.post('project-delete', {'confirm': 'yes'}, id=project_id)[0] == 200, 'Suppression du projet')
    project_id = None
    check(alice.request('task-edit', id=cascade_id)[0] == 404, 'Suppression en cascade des tâches')
    check(len(ids(alice.request('projects')[1], 'project')) == 5, 'Données de démonstration conservées')
    check(alice.post('logout')[0] == 200, 'Déconnexion')
    check('Bienvenue' in alice.request('tasks')[1], 'Session protégée après déconnexion')
    print(f'\n{CHECKS} vérifications réussies pour {BASE}')
finally:
    if project_id:
        alice.post('project-delete', {'confirm': 'yes'}, id=project_id)
