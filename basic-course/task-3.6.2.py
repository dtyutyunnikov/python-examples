"""
Имеется набор файлов, каждый из которых, кроме последнего, содержит имя следующего файла.
Первое слово в тексте последнего файла: "We".

Скачайте предложенный файл. В нём содержится ссылка на первый файл из этого набора.
"""

import requests

with open('in.txt') as file:
    r = requests.get(file.readline().strip())

base_url = 'https://stepic.org/media/attachments/course67/3.6.3/'

while True:
    r = requests.get(base_url + r.text.strip())
    if r.text.startswith('We'):
        print(r.text)
        break

