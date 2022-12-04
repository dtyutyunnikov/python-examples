obj = input()
x = int(input())
if obj == 'круг':
    res = 3.14 * x ** 2
elif obj == 'прямоугольник':
    y = int(input())
    res = x * y
else:
    y = int(input())
    z = int(input())
    p = ((x + y + z) / 2)
    res = (p * (p - x) * (p - y) * (p - z)) ** .5
print(float(res))
