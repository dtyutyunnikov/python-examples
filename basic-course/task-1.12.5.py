a = int(input())
b = int(input())
c = int(input())

if a <= b <= c:
    exp = '{2}\n{0}\n{1}'
elif b <= c <= a:
    exp = '{0}\n{1}\n{2}'
elif c <= a <= b:
    exp = '{1}\n{2}\n{0}'
elif b <= a <= c:
    exp = '{2}\n{1}\n{0}'
elif c <= b <= a:
    exp = '{0}\n{2}\n{1}'
else:
    exp = '{1}\n{0}\n{2}'

print(exp.format(a, b, c))
