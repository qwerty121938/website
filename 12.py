import tkinter as tk
import random
root = tk.Tk()
canvas = tk.Canvas(root,width=400,height=600,bg="#484891")
canvas.pack()
player = canvas.create_oval(120,120,140,140,fill="#004B97")
enemies=[]

def create_enemy():
    x = random.randint(0,400)
    y = random.randint(0,400)
    enemy = canvas.create_rectangle(x,y,x+20,y+20,fill="#004B97")
    enemies.append(enemy)

def move_player(event):
    global x,y
    key = event.keysym
    x=0
    y=0
    if key =="Up":
        y=-10
    elif key=="Down":
        y=10
    elif key == "Left":
        x=-10
    elif key == "Right":
        x=10
    canvas.move(player,x,y)
root.bind("<Key>",move_player)
for _ in range(5):
    create_enemy()
    def check_collision():
        for enemy in enemies:
            pc = canvas.coords(player)
            ec = canvas.coords(enemy)
            if pc[0]<=ec[2] and pc[1]<=ec[3] and pc[2]>=ec[0] and pc[3]>=ec[1]:
                return True
            return False
def main_loop():
    if not check_collision():
        for enemy in enemies:
            canvas.move(enemy,0,5)
            if canvas.coords(enemy)[1]>500:
                canvas.delete(enemy)
                enemies.remove(enemy)
                create_enemy()
    else:
        canvas.create_text(250,250,text="ＧＡＭＥ　ＯＶＥＲ",font=("blazed",40))
    root.after(50,main_loop)
main_loop()
root.mainloop()
