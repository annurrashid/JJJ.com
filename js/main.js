const toggle = document.querySelector(".toggle");
const navigation = document.querySelector(".navigation");
const main = document.querySelector(".main");
const listItems = document.querySelectorAll(".navigation ul li");

toggle.addEventListener("click", () => {
  navigation.classList.toggle("active");
  main.classList.toggle("active");
});

// Hover effect on navigation items
listItems.forEach((item) => {
  item.addEventListener("mouseover", () => {
    listItems.forEach((i) => i.classList.remove("hovered"));
    item.classList.add("hovered");
  });
});