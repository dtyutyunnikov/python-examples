x = input()

if x.endswith('1') and not x.endswith('11'):
    print(x + ' программист')
elif x.endswith(('2', '3', '4')) and not x.endswith(('12', '13', '14')):
    print(x + ' программиста')
else:
    print(x + ' программистов')
